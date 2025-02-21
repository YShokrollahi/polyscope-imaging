# Changes made to CRImage functions:

# processAperio: select images to process based on file.size.th default to 300000


# classificationAperio: 1. to accomodate the above changes
#                       2. to reduce quality of output jpg to quality=70

library(e1071)
library(MASS)
library(foreach)
library(sgeostat)
 


classificationAperio <- function (fileLocation, filename, pathToOutputFolderImgDir, classifier, 
    pathToOutputFolderImgDirFiles, pathToOutputFolderImgDirImages, 
    blockSlice, sliceColors, sizeO, index, cancerIdentifier, 
    maxShape = 800, minShape = 40, failureRegion = 2000, KS = TRUE, 
    colors = c(), classesToExclude = c(), threshold = "otsu", 
    window = 2, classOther = NA, pathToOutputFolderImgDirStructures, 
    colorCorrection = colorCorrection, classifyStructures = FALSE, 
    pathToOutputFolderImgDirCells, ksToExclude = ksToExclude, 
    pixelClassifier = pixelClassifier, densityToExclude = densityToExclude, 
    numDensityWindows = 32, plotCellTypeDensity = TRUE, greyscaleImage = greyscaleImage, 
    penClassifier = NULL, referenceHist = NULL) 
{
    widthO = as.numeric(as.character(sizeO[1]))
    heightO = as.numeric(as.character(sizeO[2]))
    pathToFile = file.path(fileLocation, filename)
    nameFile = strsplit(filename, "\\.")[[1]][1]
    message(paste("Start: ", nameFile))
    imageData = try(segmentImage(filename = pathToFile, maxShape = maxShape, 
        minShape = minShape, failureRegion = NA, threshold = threshold, 
        window = window, colorCorrection = FALSE, classifyStructures = classifyStructures, 
        pixelClassifier = pixelClassifier, greyscaleImage = greyscaleImage, 
        penClassifier = penClassifier, referenceHist = referenceHist))
    img = imageData$image
    imgW = imageData$segmentedImage
    cellData = imageData$features
    indexWhitePixel = imageData$indexWhitePixel
    classify = imageData$classify
    slice = blockSlice[as.character(blockSlice$block) == nameFile, 
        2]
    if (classify == TRUE) {
        if (classifyStructures == TRUE) {
            structureImages = imageData$listStructures
            structures = imageData$structures
            writeImage(structureImages[[1]], file.path(pathToOutputFolderImgDirStructures, 
                paste(nameFile, "_img1.jpg", sep = "")))
            writeImage(structureImages[[2]], file.path(pathToOutputFolderImgDirStructures, 
                paste(nameFile, "_img2.jpg", sep = "")))
            writeImage(structureImages[[3]], file.path(pathToOutputFolderImgDirStructures, 
                paste(nameFile, "_img3.jpg", sep = "")))
            writeImage(structureImages[[4]], file.path(pathToOutputFolderImgDirStructures, 
                paste(nameFile, "_img4.jpg", sep = "")))
            writeImage(structureImages[[5]], file.path(pathToOutputFolderImgDirStructures, 
                paste(nameFile, "_img5_n.jpg", sep = "")))
            writeImage(structureImages[[6]], file.path(pathToOutputFolderImgDirStructures, 
                paste(nameFile, "_img6_n.jpg", sep = "")))
        }
        else {
            structures = NA
        }
        classValues = classifyCells(classifier, image = img, 
            segmentedImage = imgW, featuresObjects = cellData, 
            paint = TRUE, KS = KS, cancerIdentifier = cancerIdentifier, 
            classOther = classOther, colors = colors, classesToExclude = classesToExclude, 
            structures = structures, classifyStructures = classifyStructures, 
            ksToExclude = ksToExclude)
        classes = classValues[[1]]
        writeImage(classValues[[2]], file.path(pathToOutputFolderImgDir, 
            file.path(paste("section", slice, sep = "_"), filename)), quality=70)
        if (classifyStructures == TRUE) {
            writeImage(classValues[[3]], file.path(pathToOutputFolderImgDirStructures, 
                paste(nameFile, "_img5.jpg", sep = "")), quality=70)
            writeImage(classValues[[4]], file.path(pathToOutputFolderImgDir, 
                file.path(paste("section", slice, sep = "_"), 
                  paste(nameFile, "_structure.jpg", sep = ""))), quality=70)
            classProbs = classValues[[5]]
        }
        else {
            classProbs = classValues[[3]]
        }
        cellData = merge(classes, cellData)
        paintedCellsFinal = CRImage:::paintCells(imgW, img, as.character(cellData[, 
            "classCell"]), cellData[, "index"], classifier$levels, 
            colors = colors)
        cellValues = CRImage:::determineCellularity(cellData[, "classCell"], 
            cellData, dim(imgW), img, imgW, indexWhitePixel, 
            cancerIdentifier, classifier$levels, densityToExclude = densityToExclude, 
            numDensityWindows = numDensityWindows, plotCellTypeDensity = plotCellTypeDensity)
        cellsSubImages = cellValues[[1]]
        cellsDensityImage = cellValues[[2]]
        cellValueTable = data.frame(t(cellsSubImages), stringsAsFactors = FALSE)
        colnames(cellValueTable) = names(cellsSubImages)
        write.table(cellValueTable, file.path(pathToOutputFolderImgDirFiles, 
            file.path(paste("section", slice, sep = "_"), nameFile)), 
            sep = "\t", row.names = FALSE)
        cellDataProbs = data.frame(cellData, classProbs, stringsAsFactors = FALSE)
        write.table(cellDataProbs, file.path(pathToOutputFolderImgDirCells, 
            paste(nameFile, ".txt", sep = "")), sep = "\t", row.names = FALSE, 
            quote = FALSE)
        qualityTable = c(nameFile, NA, NA)
        qualityTableNames = c("Image", "pen", "distToRef")
        if (!is.null(penClassifier)) {
            qualityTable[2] = as.character(imageData$penLabel)
        }
        if (!is.null(referenceHist)) {
            qualityTable[3] = imageData$distToRef
        }
        existingQualityTable = read.table(file.path(pathToOutputFolderImgDir, 
            "subimageQuality.txt"), header = TRUE)
        if (colnames(existingQualityTable)[1] != "Image") {
            qualityTable = t(qualityTable)
            colnames(qualityTable) = t(qualityTableNames)
            write.table(qualityTable, file = file.path(pathToOutputFolderImgDir, 
                "subimageQuality.txt"), sep = "\t", row.names = FALSE, 
                quote = FALSE)
        }
        else {
            wholeQualityTable = rbind(existingQualityTable, qualityTable)
            write.table(wholeQualityTable, file = file.path(pathToOutputFolderImgDir, 
                "subimageQuality.txt"), sep = "\t", row.names = FALSE, 
                quote = FALSE)
        }
        message("Cell values written")
        message("Write tumour density heatmap")
        CRImage:::writeDensityImage(pathToOutputFolderImgDir, cellsDensityImage, 
            sizeO, blockSlice, "smallDensityImage.jpg", nameFile)
        if (plotCellTypeDensity == TRUE) {
            cellsTypeImage = cellValues[[3]]
            CRImage:::writeDensityImage(pathToOutputFolderImgDir, cellsTypeImage, 
                sizeO, blockSlice, "cellTypeImage.jpg", nameFile)
        }
        message(paste(nameFile, " finished"))
        cellData
    }
    else {
        message(paste(nameFile, " finished"))
        classifiedCells = data.frame(stringsAsFactors = FALSE)
    }
}
assignInNamespace("classificationAperio", classificationAperio, "CRImage")


processAperio <- function (classifier = classifier, inputFolder = inputFolder, 
    outputFolder = outputFolder, identifier = identifier, numSlides = numSlides, 
    cancerIdentifier = cancerIdentifier, classOther = NA, maxShape = 800, 
    minShape = 40, failureRegion = 2000, slideToProcess = NA, 
    KS = TRUE, colors = c(), classesToExclude = c(), threshold = "otsu", 
    window = 2, colorCorrection = colorCorrection, classifyStructures = FALSE, 
    ksToExclude = c(), pixelClassifier = NA, densityToExclude = c(), 
    numDensityWindows = 32, resizeFactor = 4, plotCellTypeDensity = TRUE, 
    greyscaleImage = 0, penClassifier = NULL, referenceHist = NULL, 
    fontSize = 10,
    file.size.th=300000) 
{
    options(stringsAsFactors = FALSE)
    pathToFolder = inputFolder
    pathToOutputFolder = outputFolder
    pathToOutputFolderTempFiles = file.path(pathToOutputFolder, 
        "tempFiles")
    dir.create(pathToOutputFolderTempFiles)
    dir.create(pathToOutputFolder)
    pathToOutputFolderImgDir = file.path(pathToOutputFolder, 
        "classifiedImage")
    pathToOutputFolderImgDirFiles = file.path(pathToOutputFolder, 
        "Files")
    pathToOutputFolderImgDirStructures = file.path(pathToOutputFolder, 
        "Structures")
    pathToOutputFolderImgDirCells = file.path(pathToOutputFolder, 
        "Cells")
    dir.create(pathToOutputFolderImgDir)
    dir.create(pathToOutputFolderImgDirFiles)
    dir.create(pathToOutputFolderImgDirStructures)
    dir.create(pathToOutputFolderImgDirCells)
    sliceSizeList = try(findSlices(inputFolder, pathToOutputFolder, 
        numSlides, fontSize = fontSize))
    blockSlice = sliceSizeList[[1]]
    sizeO = sliceSizeList[[2]]
    smallImage = sliceSizeList[[3]]
      
    densityImage = mat.or.vec(round(dim(smallImage)[1] * resizeFactor), 
        round(dim(smallImage)[2] * resizeFactor))
    densityImage[, ] = 1
    densityImageRGB = rgbImage(red = densityImage, green = densityImage, 
        blue = densityImage)
    densityImageRGB[, , 1] = 1
    densityImageRGB[, , 2] = 1
    densityImageRGB[, , 3] = 1
    writeImage(densityImageRGB, file.path(pathToOutputFolderImgDir, 
        "smallDensityImage.jpg"))
    if (plotCellTypeDensity == TRUE) {
        writeImage(densityImageRGB, file.path(pathToOutputFolderImgDir, 
            "cellTypeImage.jpg"))
    }
    qualityTable = t(c("NA", "NA", "NA"))
    write.table(qualityTable, file.path(pathToOutputFolderImgDir, 
        "subimageQuality.txt"), sep = "\t", row.names = FALSE)
    numberSlices = length(unique(blockSlice[, 2]))
    sliceFolder = c()
    for (i in 1:numberSlices) {
        dir.create(file.path(pathToOutputFolderImgDirFiles, paste("section", 
            i, sep = "_")))
        dir.create(file.path(pathToOutputFolderImgDir, paste("section", 
            i, sep = "_")))
    }
    sliceColors = col2rgb(c("red", "blue", "green", "yellow", 
        "orange"))
    filenames = list.files(path = pathToFolder, pattern = identifier)

    Dir0 <- getwd()
    setwd(pathToFolder)
    s <- file.info(filenames)$size
    setwd(Dir0)
    filenames <- filenames[s>file.size.th]

    
    allCells = foreach(i = 1:length(filenames), .combine = rbind) %do% 
        {
            nameFile = strsplit(filenames[i], "\\.")[[1]][1]
            if (is.na(slideToProcess)) {
                classificationError = try(CRImage:::classificationAperio(pathToFolder, 
                  filenames[i], pathToOutputFolderImgDir, classifier, 
                  pathToOutputFolderImgDirFiles, pathToOutputFolderImgDir, 
                  blockSlice, sliceColors, sizeO, i, cancerIdentifier, 
                  maxShape = maxShape, minShape = minShape, failureRegion = failureRegion, 
                  KS = KS, colors = colors, classesToExclude = classesToExclude, 
                  threshold = threshold, window = window, classOther = classOther, 
                  pathToOutputFolderImgDirStructures, colorCorrection = colorCorrection, 
                  classifyStructures = classifyStructures, pathToOutputFolderImgDirCells, 
                  ksToExclude = ksToExclude, pixelClassifier = pixelClassifier, 
                  densityToExclude = densityToExclude, numDensityWindows = numDensityWindows, 
                  plotCellTypeDensity = plotCellTypeDensity, 
                  greyscaleImage = greyscaleImage, penClassifier = penClassifier, 
                  referenceHist = referenceHist))
            }
            else {
                slice = blockSlice[as.character(blockSlice$block) == 
                  nameFile, 2]
                if (slice == slideToProcess) {
                  classificationError = try(CRImage:::classificationAperio(pathToFolder, 
                    filenames[i], pathToOutputFolderImgDir, classifier, 
                    pathToOutputFolderImgDirFiles, pathToOutputFolderImgDir, 
                    blockSlice, sliceColors, sizeO, i, cancerIdentifier, 
                    maxShape = 800, minShape = 40, failureRegion = 2000, 
                    KS = KS, colors = colors, classesToExclude = classesToExclude, 
                    threshold = threshold, window = window, classOther = classOther, 
                    pathToOutputFolderImgDirStructures, colorCorrection = colorCorrection, 
                    classifyStructures = classifyStructures, 
                    pathToOutputFolderImgDirCells, ksToExclude = ksToExclude, 
                    pixelClassifier = pixelClassifierClassifier, 
                    densityToExclude = densityToExclude, numDensityWindows = numDensityWindows, 
                    plotCellTypeDensity = plotCellTypeDensity, 
                    greyscaleImage = greyscaleImage, penClassifier = penClassifier, 
                    referenceHist = referenceHist))
                }
                else {
                  message(paste("Subimage", paste(nameFile, "is not processed.")))
                }
            }
        }
    write.table(allCells, file.path(pathToOutputFolderImgDir, 
        paste("result", ".txt", sep = "")), col.names = TRUE, 
        sep = "\t", row.names = FALSE)
}

assignInNamespace("processAperio", processAperio, "CRImage")
