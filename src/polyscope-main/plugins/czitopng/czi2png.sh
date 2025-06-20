#!/bin/bash

maxBytesPerStrip=200000000
sharpenRadius=5
sharpenAmount=2

fileName="$1"
pluginRoot="/var/www/plugins/czitopng/"
inputPath="${pluginRoot}/input/"
libPath="${pluginRoot}/lib/"

cziCMD="${libPath}/libCZI/Src/CZICmd/CZIcmd"
pngWriter="${libPath}/PNGStripWriter.jar"
unsharp="${libPath}/sharpen"

inImagePath="${inputPath}/${fileName}"
outImagePath="${inImagePath%.czi}.png"
stripPath="${inputPath}/$(uuidgen)"

if [[ -f $inImagePath ]]; then
    info=$("$cziCMD" --command PrintInformation --info-level Statistics --source "$inImagePath")

    IFS=' ' read -r -a infoList <<< ${info#*Bounding-Box:}

    if [[ "${infoList[5]}" == "Layer0:" ]]; then
        X0=${infoList[1]#X=}
        X1=${infoList[6]#X=}

        Y0=${infoList[2]#Y=}
        Y1=${infoList[7]#Y=}

        W=${infoList[8]#W=}
        H=${infoList[9]#H=}
    else
        X0=0
        X1=0

        Y0=0
        Y1=0

        W=${infoList[3]#W=}
        H=${infoList[4]#H=}
    fi

    if ! [[ $W =~ '^[0-9]+$' || $H =~ '^[0-9]+$' || $X0 =~ '^[0-9]+$' || $X1 =~ '^[0-9]+$' || $Y0 =~ '^[0-9]+$' || $Y1 =~ '^[0-9]+$' ]]; then
        X=$((X1-X0))
        Y=$((Y1-Y0))

        rowsPerStrip=$((maxBytesPerStrip/W))
        rowsPerStrip=$((rowsPerStrip<H?rowsPerStrip:H))
        nStrips=$(((H-1)/rowsPerStrip))

        stripIndices=$(seq -f "%0${#nStrips}g" 0 $nStrips)

        mkdir "$stripPath"

        stripImageNameFormat="${stripPath}/${fileName}_strip_"

        for strip in $stripIndices; do
            rows=$((((10#$strip)+1)*rowsPerStrip<=H?rowsPerStrip:H-((10#$strip)*rowsPerStrip)))
            stripImagePath="${stripImageNameFormat}${strip}"
            stripFiles[$((10#$strip))]="${stripImagePath}.PNG"

            "$cziCMD" --command ChannelComposite --background 1 --rect rel\(0,$((((10#$strip)*rowsPerStrip)+Y)),"$W","$rows"\) --source "$inImagePath" --output "$stripImagePath"
            "$unsharp" -r "$sharpenRadius" -a "$sharpenAmount" "${stripFiles[$((10#$strip))]}"
        done

        java -classpath ".:$pngWriter" pngstripwriter.PNGStripWriter "$W" "$H" "${stripFiles[@]}" "$outImagePath"
        rm "${stripFiles[@]}"
        rmdir "$stripPath"
        rm "$inImagePath"
    else
        (>&2 echo "$info")
        (>&2 echo "CZI2PNG Conversion Error: Input file \"$inImagePath\" is malformed.")
        exit 2
    fi
else
    (>&2 echo "CZI2PNG Conversion Error: Input file \"$inImagePath\" not found.")
    exit 1
fi
