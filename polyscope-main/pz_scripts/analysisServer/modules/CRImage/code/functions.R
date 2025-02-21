# Common functions useful for most image processing tasks in Projects
#library(ReadImages)
library(CRImage)                                        # Run CRImage
# 1: before running CRImage:
#!!! change the FinalScan.ini using textedit in line 28
#tDescription=Aperio Image Library v12.0.14   12566x22785
#one less space in the end!!
if(class(try(oldcrimage))=='try-error'){
parseFinalScan <- function(file){
    d.temp = scan(file, what = "character", sep = "\n")
    positionMatrix = data.frame(stringsAsFactors = FALSE)
    level = FALSE
    for (line in d.temp) {
        if (substr(line, 1, 12) == "tDescription") {
            description = strsplit(line, " ")[[1]]
            size = description[7]
            size = strsplit(description[7], "x")
            width = size[1]
            height = size[2]
            row = c("size", width[[1]], height[[1]])
            positionMatrix = row
        }
        if (substr(line, 1, 8) == "[Level0]") {
            level = TRUE
        }
        if (substr(line, 1, 1) == "[" && level == TRUE) {
            row = c()
            splittedLine1 = strsplit(line, "\\[")[[1]]
            splittedLine2 = strsplit(splittedLine1[2], "\\]")[[1]]
            name = splittedLine2
        }
        if (substr(line, 1, 1) == "x" && level == TRUE) {
            splittedLine1 = strsplit(line, "=")[[1]]
            xValue = splittedLine1[[2]]
        }
        if (substr(line, 1, 1) == "y" && level == TRUE) {
            splittedLine1 = strsplit(line, "=")[[1]]
            yValue = splittedLine1[[2]]
            row = c(name, xValue, yValue)
            positionMatrix = rbind(positionMatrix, row)
        }
    }
    positionMatrix
}
assignInNamespace("parseFinalScan", parseFinalScan, "CRImage")
}
replace.vector <-function(x, tochange=unique(x), toreplace=1:length(unique(x))){
	for (y in 1:length(tochange))  x=replace(x, list=(x==tochange[y]), toreplace[y])
	x
}
Paste <- function(x,...) paste(x, sep='', ...)


writeImageContour <- function(img, z, file='contourImg.jpg', q=c(0.98, 0.95, 0.9)){
  require(EBImage)
  q <- quantile(z[z!=0], q)
  mask <- img
  for (i in q){
    th <- i
    mask1 = bwlabel(z > th)
    mask = paintObjects(mask1, mask, col='black')
  }
  writeImage(mask, file=file)
}
getDensityMap <- function(mat=NULL, xy=NULL, lenx=200, leny=200, h=c(50,50),  display='contour', ...){
  require(sm)
  if (is.null(xy))
    xy <- getxy(mat)
  par(mar=c(0,0,0,0))
  res <- sm.density(cbind(xy$x, xy$y), display=display, h=h, eval.points=cbind(seq(1, max(xy$x), len=lenx), seq(1, max(xy$y), len=leny)), xlab='', ylab='', ...)
  points(xy$x, xy$y, cex=.5, pch='.')
  res
}



processImage <- function(imgFile, resultFile='processedImage', writeCellTable=TRUE, saveSeg=FALSE, s=0){
#  This function runs CRImage on one image and output processed image, segmentation data, and cell file
  require(EBImage)
  require(CRImage)
  classifierName <- 'classifier_3.3_and_Da2' 
  load(paste('~/Google Drive/YuanLab_ShareFolder/Projects/Code/CRImage/', classifierName, sep=''))
  classifier <- model
  source('~/Google Drive/YuanLab_ShareFolder/Projects/Code/CRImage_modifications.R')

  img <- readImage(imgFile)
  if (s!=0){
    bimg <- blur(img, s=s)
  }else{
    bimg <- img
  }
  tmp <- try(load(paste(resultFile, '_seg.rdata', sep='')), silent=TRUE)
  if (class(tmp)=='try-error')
    imageData <- try(CRImage:::segmentImage(image=bimg,maxShape=800,minShape=40,failureRegion=2000,threshold="otsu",  greyscaleImage=1, classifyStructures=FALSE))
  if (class(tmp)=='try-error' & saveSeg)
    save(imageData, file=paste(resultFile, '_seg.rdata', sep=''))
  if(length(imageData)>1)
    if(nrow(imageData[[3]])!=0){
      tab <- classifyCells(classifier=classifier, filename='', image=img, segmentedImage=imageData[[2]], imageData[[3]], KS=FALSE,maxShape=900, minShape=40, failureRegion=2000,  colors=c("white", "green","blue","red")) 
      writeImage(tab[[2]], file=paste(resultFile, '_classified.jpg', sep=''), quality=60)
      if (writeCellTable)
        write.table(cbind(tab[[1]],imageData[[3]][,-1], tab[[3]]), file=paste(resultFile, '_cells.txt', sep=''), quote=F, row.names=F, sep='\t')
    }
}


sampling <- function(ffs=NULL, desDir='../Sampling', n=1){
#  Sampling subimages from directories
#  for each directory/sample, n subimages largest in size are sampled 
  dir.create(desDir, recursive=T)
  if (is.null(ffs))
    ffs <- dir()
  idx <- file.info(ffs)
  ffs <- ffs[idx$isdir]
  for (ff in ffs){
    setwd(ff)
    idx <- file.info(dir())$size
    f <- dir()[grepl('Da', dir())]
    idx <- idx[grepl('Da', dir())]
    f <- f[sort.list(idx, decreasing=T)[1:n]]
    print(paste('[sampling] Source: ',f,' Target: ',paste(desDir, '/', ff, '_', f, sep='')))
    sapply(f, function(x) file.copy(from=x, to=paste(desDir, '/', ff, '_', x, sep='')))
    setwd('../')
  }
}

testSampling <- function(Dir='./Sampling', desDir='./SamplingClassified', s=0, ...){
# set s if blurring is needed
  ffs <- dir(Dir)
  dir.create(desDir)  
  for (ff in ffs){
    ff <- strsplit(ff, split='.jpg', fixed=T)[[1]][1]
    processImage(paste(Dir, '/', ff, '.jpg', sep=''), paste(desDir, '/', ff, 's',s, sep=''), s=s, ...)
    gc()
  }
}
plotCRImageCellDots <- function(tab, imageData, classes, classColors, cex=1, pch='.', ...){
  par(mar=c(0,0,0,0))
  plot.imagematrix(imagematrix(aperm(imageData[[1]], c(2,1,3))))
  points(imageData[[3]][tab[[1]][,1],2], nrow(imageData[[2]])-imageData[[3]][tab[[1]][,1],3], col=replace.vector(as.character(unlist(tab[[1]][,2])), classes, classColors), cex=cex, pch=pch,...)
}



colorBar <- function(x, f='colorbar.pdf', col=NULL, br=NULL, brief=TRUE, ...){
  pdf(f, height=3, width=.8)
  par(mar=c(.1,.1,.1,3.2))
  if (is.null(br))
    br <- quantile(x, prob=seq(0,1,len=21), na.rm=TRUE)
  n <- length(br)
  if (is.null(col))
    col <- color.code(n-1)

  if(brief){
    br <- br[c(seq(1, n, by=3),n)]
    col <- col[seq(1, n, by=3)]
    n <- length(br)
  }
    
  image(matrix(1:(n-1), nrow=1), col=col, axes=FALSE)
  axis(4, at=(1:n -1)/(n-2), paste(signif(br,1), sep=''), las=2, cex=.7)
  if(f!='')
    dev.off()
}

getFeatures <- function(imgW, img, deleteBorder=T){
    hF = hullFeatures(imgW)[,-c(1,2,10)]
    allM = moments(imgW, img[,,1])[,-c(1,2,10)]
    zM = zernikeMoments(imgW, img[,,1])
    hFgrey = haralickFeatures(imgW, img[,,1])
    fea <- cbind(hF, allM, zM, hFgrey)
    if (deleteBorder){
         borderLeft = as.vector(unique(imgW[, 1]))
         borderRight = as.vector(unique(imgW[, dim(imgW)[2]]))
         borderUp = as.vector(unique(imgW[1, ]))
         borderDown = as.vector(unique(imgW[dim(imgW)[1], ]))
         borderCells = setdiff(unique(c(borderLeft, borderRight, borderUp, borderDown)), 0)
         if (length(borderCells))
           fea <- fea[-c(borderCells),]
       }
    list(fea=fea, borderCells=borderCells)
}
    
getCRImageFeatures <- function(imgW, img, r=1, deleteBorder=T){
  require(MASS)
  hF = hullFeatures(imgW) 
  allM = moments(imgW, img[,,r]) 
  zM = zernikeMoments(imgW, img[,,r])
  hFgrey = haralickFeatures(imgW, img[,,r])
    allFeatures <- cbind(hF, allM, zM, hFgrey)
    allFeatures = allFeatures[allFeatures[, "g.s"] >  0, ]
    index = 1:dim(allFeatures)[1]
    allFeatures = cbind(index, allFeatures)
    cellCoordinates = allFeatures[, c("g.x", "g.y")]
    cellCoordinates[, 1] = as.numeric(format(cellCoordinates[, 
                     1], digits = 4))
    cellCoordinates[, 2] = as.numeric(format(cellCoordinates[, 
                     2], digits = 4))
    densityNeighbors = kde2d(cellCoordinates[, "g.x"], 
      cellCoordinates[, "g.y"], n = 100)
    dV = densityNeighbors$z
    dimnames(dV) = list(densityNeighbors$x, densityNeighbors$y)
    densityValues = interpolate(cellCoordinates, dV)
    allFeatures = cbind(allFeatures, densityValues)
  indexWhitePixel = which(img[, , 1] > 0.85 & img[, , 2] > 
            0.85 & img[, , 3] > 0.85)
    sizeCytoplasma = CRImage:::segmentCytoplasma(img, imgW, indexWhitePixel, 
      img[,,r], index, hF)
    # numberNeighbors = CRImage::numberOfNeighbors(img, cellCoordinates, allFeatures)
  numberNeighbors=rep(1, nrow(allFeatures))
  allFeatures = cbind(allFeatures, numberNeighbors)
    allFeatures = cbind(allFeatures, sizeCytoplasma)
    allFeatures = allFeatures[allFeatures[, "g.s"] > 
      0, ]
    
  if (deleteBorder){
         borderLeft = as.vector(unique(imgW[, 1]))
         borderRight = as.vector(unique(imgW[, dim(imgW)[2]]))
         borderUp = as.vector(unique(imgW[1, ]))
         borderDown = as.vector(unique(imgW[dim(imgW)[1], ]))
         borderCells = setdiff(unique(c(borderLeft, borderRight, borderUp, borderDown)), 0)
         if (length(borderCells))
           allFeatures <- allFeatures[-c(borderCells),]
       }
    list(fea=allFeatures, borderCells=borderCells)
}


reduceSize <- function(Dir, quality=50){
  for (ff in dir(Dir))
    if (grepl('.jpg', ff)){
    img <- readImage(paste(Dir, '/', ff, sep=''))
    writeImage(img, paste(Dir,'/', ff, sep=''), quality=quality)
  }
}
ifNotWhiteImage <- function(img){
    if (length(dim(img)) > 2) {
        imgT = img[, , 1]
        whitePixelMask = img[, , 1] > 0.85 & img[, , 2] > 0.85 & 
            img[, , 3] > 0.85
        indexWhitePixel = which(img[, , 1] > 0.85 & img[, , 2] > 
            0.85 & img[, , 3] > 0.85)
    }
    else {
        imgT = img
        indexWhitePixel = which(img > 0.85)
    }
    numWhitePixel = length(imgT[indexWhitePixel])
    numPixel = length(as.vector(imgT))
    (numWhitePixel/numPixel) < 0.9
}


paint <- function(x, img, col='white'){
  colorMode(x) <- 'Grayscale'
  res <- paintObjects(x, tgt=img, col=col)
  display(res)
  res
}
removeSmallObj <- function(x, size=10){
  bw <- bwlabel(x)
  fea <- hullFeatures(as.array(bw))
  s <- which(fea[,'g.s'] < size)
  x[bw%in%s] <- FALSE
  x
}

segNuclei <- function(b1, file='nuclei.jpg'){
  h = thresh(b1, 20, 20, 0.1)
  l = thresh(b1, 10, 10, 0.02)

  n2 = opening(h, makeBrush(2, shape='disc'))
  nmask = bwlabel(n2)

  ctmask = opening(l, makeBrush(2, shape='disc'))
  cmask = propagate(b1, seeds=n2, l, lambda=0.1)
  cmask
}

getxy <- function(mat){
  mat <- mat!=0
  y <- which(mat)%%nrow(mat)
  x <- which(mat)%/%nrow(mat)+1
  list(x=x, y=y)
}

matrix2density <- function(m, h=5){
  require(splancs)
  p <- getxy(m)
  p <- cbind(p$x, p$y)
  res <- kernel2d(p, poly=cbind(c(0, 0, nrow(m), nrow(m)), c(0, ncol(m), ncol(m), 0)), h0=h, nx=nrow(m), ny=ncol(m))
  res
}

getKstatsScore <- function(files){
  auc <- matrix(NA, nrow=length(files), ncol=3, dimnames=list(files, c('cancer', 'lymphocyte', 'stromal')))
  auc.sd <- auc
  l <- NULL
  Area <- NULL
  nc <- NULL; nl <- NULL; ns <- NULL
  for (file in as.character(files)){
    res <- try(load(paste('./ImageSpatial/SpatialStats/', file, '_2.rdata', sep='')))
    if (class(res)!='try-error')
      if ((!is.na(cc.s[1]))){
        l <- c(l, length(kstats.s))
    #kstats.c <- apply(cc.c[,1:101], 1, function(x) trap.rule(0:100, x))
    #kstats.l <- apply(cc.l[,1:101], 1, function(x) trap.rule(0:100, x))
    #kstats.s <- apply(cc.s[,1:101], 1, function(x) trap.rule(0:100, x))
    #auc[file,1] <- median(kstats.c)
    #auc[file,2] <- median(kstats.l)
        auc[file,3] <- median(kstats.s)
    #auc.sd[file,1] <- sd(kstats.c)
   # auc.sd[file,2] <- sd(kstats.l)
        auc.sd[file,3] <- sd(kstats.s)
    #load(paste('./ImageSpatial2/CellPosAndMask/', file, '.rdata', sep=''))
    #Area <- c(Area, sum(blockSlice$Area))
    #nc <- c(nc, nCell.c)
    #ns <- c(ns, nCell.s)
    #nl <- c(nl, nCell.l)
      }
  }
  auc <- auc/1000000
  auc.sd <- auc.sd/1000000
  colnames(auc) <- c('Kstats.cancer', 'Kstats.lym', 'Kstats.stromal')
  list(auc=auc, auc.sd=auc.sd)
}

testForBatchEffectPlots <- function(dat, main=''){
  require(limma)
  dat <- scale(dat)
  dat[is.na(dat)] <- 0
  sf <- c('g.sf', 'g.I1', 'g.acirc', 'g.ecc')
  shapefeatures <- as.vector(sapply(sf, function(x) grep(x, colnames(dat))))
  sig <- rowMeans(dat[,shapefeatures])
  idx <- sort.list(as.numeric(allData$file))
  plot(sig[idx], pch=19, col='grey', main=main, xlab='samples sorted by batches', ylab='Mean of shape features')
}  

  
plotERNFfea <- function(dat){
  erfea <- apply(dat, 2, function(x) t.test(x[Set[[2]]], x[Set[[3]]])$p.value)
  plot(-log10(erfea), pch=19, xlab='features', main='ER+ vs ER-', ylab='-log10(p-value) from t-test', ylim=range(-log10(erfea))+c(0,1))
  text(x=which(erfea<0.01), y=-log10(erfea[erfea<0.01]), names(erfea)[erfea<0.01], pos=3, srt=90)
}

processNFDat <- function(dat){ 
  removefeatures <- which(apply(dat,2,sd)>15)
  dat <- dat[,-removefeatures]
  scale(dat)
}


getClassificationConfidence <- function(Dir){
  files <- dir(Dir)
  mm <- NULL
  dd <- NULL
  for (ff in files){
    cellFiles <- dir(paste(Dir, '/', ff, '/Cells', sep=''))
    pp <- NULL
    for (cellFile in cellFiles){
      cells <- read.table(paste(Dir, '/', ff, '/Cells/', cellFile, sep=''), header=TRUE)
      pp <- rbind(pp, cbind(as.character(cells$classCell), rowMax(cells[,c('c', 'a', 'l', 'o')])))
    }
    
    if (!is.null(dim(pp))){
      tmp <- aggregate(as.numeric(pp[,2]), list(pp[,1]), mean)
      mm <- rbind(mm, tmp[match(c('c','l','o','a'),tmp[,1]),2])
      tmp <- aggregate(as.numeric(pp[,2]), list(pp[,1]), sd)
      dd <- rbind(dd, tmp[match(c('c','l','o','a'),tmp[,1]),2])
    }else{
      mm <- rbind(mm, c(NA, NA, NA, NA))
      dd <- rbind(dd, c(NA, NA, NA, NA))
    }    
  }
  rownames(mm) <- files
  colnames(mm) <- c('cancer', 'lym', 'stromal', 'artifact') 
  rownames(dd) <- files
  colnames(dd) <- c('cancer', 'lym', 'stromal', 'artifact')
  list(mean=mm, sd=dd)
}




getCellFiles <- function(ff){
  cells <- scan(file=ff, what=c('numeric', 'character', rep('character', 105)), fill=TRUE, quiet=TRUE)
  n <- length(cells)
  cells <- matrix(cells, ncol=105, byrow=TRUE)
  if (n%%105 != 0 )
    cells <- cells[-nrow(cells),]
  colname <- cells[1,-c(1:2)]
  rowname <- cells[-1,2]
  cells <- cells[-1, -c(1:2)]
  cells <- matrix(as.numeric(cells), ncol=103, dimnames=list(rowname,colname)) 
  cells
}

MixComponents <- function(x, k=4, G=NULL){
  require(prabclus)
  require(mclust)
  y <- NNclean(x,k=k)
  Mclust(x,G=G, initialization=list(noise=y$z==1))
  #Mclust(x[y$z==1],G=G)
}
getSummary <- function(x, k=1:5, noiseknn=2){
  require(moments)
  list(sum=sum(x,na.rm=T), median=median(x,na.rm=T), sd=sd(x,na.rm=T), skewness=skewness(x,na.rm=T), kurtosis=kurtosis(x,na.rm=T), mixture=try(MixComponents(x,k=noiseknn,G=k)$G))
}


getNucleiFeature <- function(Dir){
  files <- dir(Dir)
  library(e1071)
  library(snow)
  features <- colnames(read.table(paste(Dir, '/', files[1], '/Cells/',  dir(paste(Dir, '/', files[1], '/Cells', sep=''))[1], sep=''), header=TRUE))
  features <- features[-c(1,3:4, (length(features)-3):length(features))]
  
  mm <- array(NA, c(length(files), 4, length(features)-1), dimnames=list(files, c('cancer', 'lym', 'stromal', 'artifact'), features[-1]))# files x cell types x features
  dd <- mm
  me <- mm
  sk <- mm
  mml <- mm
  ddl <- mm
  mel <- mm
  skl <- mm

  options(cluster=makeCluster(4, 'SOCK'))
  for (ff in files){
    cellFiles <- dir(paste(Dir, '/', ff, '/Cells', sep=''))
    pp <- NULL
    gc()
    for (cellFile in cellFiles){
      cells <- try(read.table(paste(Dir, '/', ff, '/Cells/', cellFile, sep=''), header=TRUE))
      if (class(cells)!='try-error')
        if(all(features %in% colnames(cells)))
          pp <- rbind(pp, cells[, features])
    }
      
    if (!is.null(dim(pp))){
      for (i in 1:(length(features)-1)){
        tmp <- aggregate(as.numeric(pp[,i+1]), list(pp[,1]), function(x) mean(x, na.rm=TRUE))
        mm[ff,,i] <- tmp[match(c('c','l','o','a'),tmp[,1]),2]
        tmp <- aggregate(as.numeric(pp[,i+1]), list(pp[,1]), function(x) median(x, na.rm=TRUE))
        me[ff,,i] <- tmp[match(c('c','l','o','a'),tmp[,1]),2]
        tmp <- aggregate(as.numeric(pp[,i+1]), list(pp[,1]), function(x) sd(x, na.rm=TRUE))
        dd[ff,,i] <- tmp[match(c('c','l','o','a'),tmp[,1]),2]
        tmp <- aggregate(as.numeric(pp[,i+1]), list(pp[,1]), function(x) skewness(x, na.rm=TRUE))
        sk[ff,,i] <- tmp[match(c('c','l','o','a'),tmp[,1]),2]
      }

      pp[,-1] <- log(pp[,-1]) 
      for (i in 1:(length(features)-1)){
        tmp <- try(aggregate(as.numeric(pp[pp[,i+1]!=-Inf,i+1]), list(pp[pp[,i+1]!=-Inf,1]), function(x) mean(x, na.rm=TRUE)))
        if(class(tmp)!='try-error')
          if(nrow(tmp)!=0){
          mml[ff,,i] <- tmp[match(c('c','l','o','a'),tmp[,1]),2]
        tmp <- aggregate(as.numeric(pp[pp[,i+1]!=-Inf,i+1]), list(pp[pp[,i+1]!=-Inf,1]), function(x) median(x, na.rm=TRUE))
        mel[ff,,i] <- tmp[match(c('c','l','o','a'),tmp[,1]),2]
        tmp <- aggregate(as.numeric(pp[pp[,i+1]!=-Inf,i+1]), list(pp[pp[,i+1]!=-Inf,1]), function(x) sd(x, na.rm=TRUE))
        ddl[ff,,i] <- tmp[match(c('c','l','o','a'),tmp[,1]),2]
        tmp <- aggregate(as.numeric(pp[pp[,i+1]!=-Inf,i+1]), list(pp[pp[,i+1]!=-Inf,1]), function(x) skewness(x, na.rm=TRUE))
        skl[ff,,i] <- tmp[match(c('c','l','o','a'),tmp[,1]),2]
      }      
    }}
    res <- list(mean=mm, sd=dd, median=me, skewness=sk, meanl=mml, sdl=ddl, medianl=mel, skewnessl=skl)
    save(res, file='tmpRes.rdata')
  }
  stopCluster(getOption('cluster'))
  options(cluster=NULL)
  list(mean=mm, sd=dd, median=me, skewness=sk, meanl=mml, sdl=ddl, medianl=mel, skewnessl=skl)
}


Normalize <- function(x)  (x - min(x))/(max(x)-min(x))



trap.rule <- function(x,y){
  set <- is.na(x) | is.na(y)
  x <- x[!set]
  y <- y[!set]
  sum(diff(x)*(y[-1]+y[-length(y)]))/2
}

spatialAUC <- function(cellPos, img, type='c', method=c('Kest', 'Kinh'),
                       correction=c('trans', 'border', 'bord.modif')){
  require(spatstat)
  cellPos <- cellPos[cellPos[,2]==type, 3:4]
  mask <- !(img[, , 1] > 0.85 & img[, , 2] > 0.85 & img[, , 3] > 0.85) ## mask holes!
  if (!is.logical(mask[1]))
    mask <- matrix(as.logical(mask), nrow(mask), ncol(mask))
  w <- owin(mask=t(mask)) ## important!
  x <- as.numeric(as.character(cellPos[,1]))
  y <- as.numeric(as.character(cellPos[,2]))
  pp <- as.ppp(cbind(x, y), w)
  nExcludPoint <- length(x) - length(pp$x)
  if(nExcludPoint>0) print(nExcludPoint)
  if ('Kest'%in%method)
    kstats <- Kest(pp, r=0:500, nlarge=5000, correction=correction)
  if ('Kinh'%in%method)
    kinh <- Kinhom(pp, r=0:500, sigma=100, nlarge=5000, correction=correction)
  #jstats <- Jest(pp,  r=0:500, correction='km')  
  #tt <- quadrat.test(pp) #Chi-squared test of CSR using quadrat counts
  list(K.trans=trap.rule(kstats$r, kstats$trans-kstats$theo),
       K.border=trap.rule(kstats$r, kstats$border-kstats$theo),
       K.bord.modif=trap.rule(kstats$r, kstats$bord.modif-kstats$theo),
       kstats=kstats,
       Kinh.trans=trap.rule(kinh$r, kinh$trans-kinh$theo),
       Kinh.border=trap.rule(kinh$r, kinh$border-kinh$theo),
       Kinh.bord.modif=trap.rule(kinh$r, kinh$bord.modif-kinh$theo),
       Kinh=kinh,
       #Jauc=trap.rule(jstats$r, jstats$km-jstats$theo), jstats=jstats,
       #test=tt$p.value,
       nExcludPoint=nExcludPoint,
       pp=pp)
}

extractTumourCluster <- function(cellPos, type='c',outputFolder='./', file='TumourCluster'){
  require(splancs)
  cl <- as.character(cellPos[,1])
  x <- as.numeric(cellPos[,2])
  y <- as.numeric(cellPos[,3])
  y <- max(y) + min(y) - y # turn upside down for plotting images
  dat <- list(x=x[cl==type], y=y[cl==type])

  res <- kernel2d(as.points(dat), poly=cbind(c(min(dat$x), min(dat$x), max(dat$x), max(dat$x)), c(min(dat$y), max(dat$y), max(dat$y), min(dat$y))), h0=18, nx=500, ny=500) 

  png(paste(outputFolder,'/', file, 'KernelDensity.png', sep=''))
  image(res, add=F, col=colorRampPalette(c('red', 'pink', 'lightblue', 'blue'))(20))
  points(as.points(dat), pch='.')
  dev.off()

  imgW <- res$z >= 0.0011
  imgW <- bwlabel(imgW)
  ft <- hullFeatures(imgW)
  imgW[imgW%in%c((1:nrow(ft))[ft[,'g.s']<20])]=0
  ft <- ft[ft[,'g.s']>=20, , drop=F]
  
  png(paste(outputFolder,'/', file, 'TumourClusters.png', sep=''))
  image(imgW, col=c('white',colorRampPalette(c('white', 'black'))(100)[50:100]))
  dev.off()

  ft
}

getTumourClusterSVM <- function(img, model, imgFile, txtFile, imgCancer=NULL){
  # for a given image and SVM model, find the tumour clusters, write a labeled image and a text file containg features of the clusters
  require(CRImage)
  img1=readImage(img)
  img=img1
  f = makeBrush(5, shape='disc', step=FALSE)
  f = f/sum(f)
  meanStdTarget=rbind(c(78.282154,300.371584) ,c(9.694320,-10.856946), c(2.081496,3.614328))
  imgCor=CRImage:::colorCorrection(img,meanStdTarget)
  img=imgCor[[1]]
  imgC=img
  imgG=filter2(imgC, f)
  colorValues=cbind(as.vector(imgG[,,1]),as.vector(imgG[,,2]),as.vector(imgG[,,3]))
  predictedClasses=predict(model,colorValues)
  imgB=array(as.numeric(as.character(predictedClasses)),dim(imgC)[1:2])
  imgS=bwlabel(imgB)
  numSeq=tabulate(imageData(imgS)+1)
  imgSdN=imageData(imgS)+1

  a=array(numSeq[imgSdN]<80,dim(imgSdN))
  imgSdN[a]=1
  imgSdN=imgSdN-1
  imgS=imgSdN
  imgP=paintObjects(imgS,img1,col=c("green"))
  writeImage(imgP, file=imgFile)

  imgS=bwlabel(imgS) 
  if (!is.null(imgCancer)){
    imgK <- readImage(imgCancer)
    imgK <- resize(imgK, w=ncol(imgS), h=nrow(imgS))
    idx <- sapply(1:max(imgS), function(x) mean(imgK[imgS==x]))
    imgS[imgS%in%which(idx>.9)] <- 0
  }
  ft <- hullFeatures(imgS)

  #writeImage(imgP, file=imgFile)
  write.table(ft[ft[,'g.s']!=0, ], file=txtFile, quote=FALSE, sep='\t')  
}


# get cellularity scores
Normalize <- function(x)  (x - min(x, na.rm=TRUE))/(max(x, na.rm=TRUE)-min(x, na.rm=TRUE))
getCellularityScore.new <- function(Dir){
# changes: only images with larger area
  files <- dir(Dir)
  fileScore <- matrix(NA, nrow=length(files), ncol=16, dimnames=list(files, c('fileName', 'whichSection', 'nTumour', 'nLymphozyte', 'nArtifact', 'nOthers', 'nTotal', 'Area', 'TumourCellRatio', 'TumourAreaRatio', 'medianTumourCellRatio', 'medianTumourAreaRatio', 'TumourCellScore', 'TumourAreaScore', 'medianTumourCellScore', 'medianTumourAreaScore')))

  for (ff in files){
    imageSections <- dir(paste(Dir, '/', ff, '/Files/', sep=''))
    nSection <- length(imageSections)
    if (nSection > 0){
      celluSection <-  ifelse(nSection==1, 1, 2)
      sectionFiles <- dir(paste(Dir, '/', ff, '/Files/', imageSections[celluSection], sep=''))
      if (length(sectionFiles)){
        Score <- NULL#matrix(0, length(sectionFiles), 7)
        idx <- NULL
        for (j in 1:length(sectionFiles)){
          scores <- try(read.table(paste(Dir,'/', ff, '/Files/', imageSections[celluSection], '/', sectionFiles[j], sep=''), header=TRUE))
          if(class(scores)!='try-error'){
            idx <- c(idx, j)
            nTumour <- ifelse('c'%in%names(scores), as.numeric(scores['c']), 0)
            nOther <- ifelse('o'%in%names(scores), as.numeric(scores['o']), 0) 
            nLym <- ifelse('l'%in%names(scores), as.numeric(scores['l']), 0)
            nArti <- ifelse('a'%in%names(scores), as.numeric(scores['a']), 0)
            nCell <- max(nLym+nOther+nTumour,1)
            Score <- rbind(Score, unlist(c(nTumour, nLym, nArti, nOther, scores['numTumourPixel'], nTumour/nCell, nTumour/scores['numTumourPixel'])))
          }}
	
        sectionTumours <- sum(as.numeric(Score[,1]))
        sectionOthers <- sum(as.numeric(Score[,4]))
        sectionLym <- sum(as.numeric(Score[,2]))
        sectionArti <- sum(as.numeric(Score[,3]))
        sectionTotal <- sum(sectionTumours, sectionOthers, sectionLym, sectionArti)
        sectionArea <- sum(as.numeric(Score[,5]))
		
        sectionScore <- c(as.numeric(ff), celluSection, sectionTumours, sectionLym, sectionArti, sectionOthers, sectionTotal, sectionArea, sectionTumours/(sectionOthers + sectionTumours + sectionLym), sectionTumours/sectionArea)
        colnames(Score) <- c("Tumour","Lympho","Artifact","Others","numTumourPixel", "tumourCellRatio", "tumourAreaRatio")
        rownames(Score) <- sectionFiles[idx]
        Score <- rbind(Score, apply(Score, 2, function(x) median(x, na.rm=TRUE)), sectionScore[c(3:6, 8:10)])
        rownames(Score)[(nrow(Score)-1):nrow(Score)] <- c('median', 'sum')
        write.table(Score, file=paste(Dir, '/', ff, '/section_', celluSection,'_Score.txt', sep=''), sep='\t', quote=F)

        Score <- Score[-c((nrow(Score)-1):nrow(Score)),]
        area <- Score[,'numTumourPixel']/1000000
        idx <-  area >= min(2, sort(area, decreasing=TRUE)[5])
        medianTumourCell <- median(tb[,'tumourCellRatio'][idx])
        medianTumourArea <- median(tb[,'tumourAreaRatio'][idx])
        fileScore[as.character(ff), 1:12] <- c(sectionScore, medianTumourCell, medianTumourArea)
}}}

  fileScore[,13:16] <- apply(fileScore[,9:12], 2, Normalize)
  as.data.frame(fileScore)
}

getCellularityScore <- function(Dir){

  files <- dir(Dir)
  fileScore <- matrix(NA, nrow=length(files), ncol=16, dimnames=list(files, c('fileName', 'whichSection', 'nTumour', 'nLymphozyte', 'nArtifact', 'nOthers', 'nTotal', 'Area', 'TumourCellRatio', 'TumourAreaRatio', 'medianTumourCellRatio', 'medianTumourAreaRatio', 'TumourCellScore', 'TumourAreaScore', 'medianTumourCellScore', 'medianTumourAreaScore')))

  for (ff in files){    
    res <- getFileCellularityScore(ff, Dir)
    Score <- res$Score
    sectionScore <- res$sectionScore
    if (!is.null(res))
      fileScore[as.character(ff), 1:12] <- c(sectionScore, Score[nrow(Score)-1, 6], Score[nrow(Score)-1, 7])
  }

  fileScore[,13:16] <- apply(fileScore[,9:12], 2, Normalize)
  as.data.frame(fileScore)
}

getFileCellularityScore <- function(ff, Dir){
    imageSections <- dir(paste(Dir, '/', ff, '/Files/', sep=''))
    nSection <- length(imageSections)
    if (nSection > 0){
      celluSection <-  ifelse(nSection==1, 1, 2)
      sectionFiles <- dir(paste(Dir, '/', ff, '/Files/', imageSections[celluSection], sep=''))
      if (length(sectionFiles)){
        Score <- NULL
        idx <- NULL
        for (j in 1:length(sectionFiles)){
          scores <- try(read.table(paste(Dir,'/', ff, '/Files/', imageSections[celluSection], '/', sectionFiles[j], sep=''), header=TRUE))
          if(class(scores)!='try-error')
            if (nrow(scores) < 3){
              idx <- c(idx, j)
              nTumour <- ifelse('c'%in%names(scores), as.numeric(scores['c']), 0)
              nOther <- ifelse('o'%in%names(scores), as.numeric(scores['o']), 0) 
              nLym <- ifelse('l'%in%names(scores), as.numeric(scores['l']), 0)
              nArti <- ifelse('a'%in%names(scores), as.numeric(scores['a']), 0)
              nCell <- max(nLym+nOther+nTumour,1)
              Score <- rbind(Score, unlist(c(nTumour, nLym, nArti, nOther, scores['numTumourPixel'], nTumour/nCell, nTumour/scores['numTumourPixel'])))
            }}
	
        sectionTumours <- sum(as.numeric(Score[,1]))
        sectionOthers <- sum(as.numeric(Score[,4]))
        sectionLym <- sum(as.numeric(Score[,2]))
        sectionArti <- sum(as.numeric(Score[,3]))
        sectionTotal <- sum(sectionTumours, sectionOthers, sectionLym, sectionArti)
        sectionArea <- sum(as.numeric(Score[,5]))
		
        sectionScore <- c(as.numeric(ff), celluSection, sectionTumours, sectionLym, sectionArti, sectionOthers, sectionTotal, sectionArea, sectionTumours/(sectionOthers + sectionTumours + sectionLym), sectionTumours/sectionArea)
        colnames(Score) <- c("Tumour","Lympho","Artifact","Others","numTumourPixel", "tumourCellRatio", "tumourAreaRatio")
        rownames(Score) <- sectionFiles[idx]
        Score <- rbind(Score, apply(Score, 2, function(x) median(x, na.rm=TRUE)), sectionScore[c(3:6, 8:10)])
        rownames(Score)[(nrow(Score)-1):nrow(Score)] <- c('median', 'sum')
        write.table(Score, file=paste(Dir, '/', ff, '/section_', celluSection,'_Score.txt', sep=''), sep='\t', quote=F)

        list(Score=Score, sectionScore=sectionScore)
}}}

getCellPos <- function(ff, orgImgDir, claImgDir, W=10, ifRemoveWhiteColumns=FALSE, AreaThresh=0, CellThresh=0, ifGetCellMor=TRUE){
# This function generates positions of all classified cells in a large image by combining classificaiton results from subimages. It will also generate a h&e image for the whole tissue, 1/W of the size of original
# W: downscale factor
# AreaThresh: thrshold for image area in subimages in pixels
# CellThresh: threshold for cell number in subimages
# ifGetCellMor: should cell morphology be included in the output cell pos files (note for large sections setting this to TRUE will result in very large cellpos file)  
  require(EBImage)
  require(jpeg)
  dir.create('CellPosAndMask', showWarnings=FALSE)
  dir.create('OutputImage', showWarnings=FALSE)
  dir.create('ImageBlockSlice', showWarnings=FALSE)
  imgDir <- paste(orgImgDir, '/', sep='')
  outputDir <- paste(claImgDir,  '/', sep='')
  classifiedImgDir <- outputDir

  sliceSizeList = try(CRImage:::findSlices(paste(imgDir,  ff, sep=''), paste(outputDir,ff, sep=''), 1))
  blockSlice = sliceSizeList[[1]]
  size0 <- sliceSizeList[[2]]
  write.table(blockSlice, file=paste('./ImageBlockSlice/', ff,'blockSlice.txt', sep=''), quote=F, sep='\t')
  tb <- getFileCellularityScore(ff=ff, Dir=classifiedImgDir)$Score

  BlockSlice <- read.table(paste('./ImageBlockSlice/', ff,'blockSlice.txt', sep=''),as.is=T)
  BlockSlice <- BlockSlice[grep('Da',BlockSlice$block), ]
  BlockSlice[,5] <- BlockSlice[,5]-min(BlockSlice[,5])
  BlockSlice[,5] <- max(BlockSlice[,5])-BlockSlice[,5]
  BlockSlice[,6] <- BlockSlice[,6]-min(BlockSlice[,6])
  BlockSlice[,6] <- max(BlockSlice[,6])-BlockSlice[,6]
  blockSlice <- BlockSlice
  subimages <- BlockSlice$block
  subimages0 <- dir(paste(classifiedImgDir,ff, '/Cells', sep=''))
  subimages0 <- sapply(subimages0, function(subimage) strsplit(subimage, split='.txt', fixed=T)[[1]][1])
  
  if(0){
  # remove images not processed 
  tb <- read.table(paste(classifiedImgDir, ff, '/section_1_Score.txt', sep=''))
  Area <- tb$numTumourPixel[match(subimages, rownames(tb))]
  Cell <- (tb$Tumour+tb$Lympho+tb$Others)[match(subimages, rownames(tb))]
  idx <- Area>AreaThresh & Cell>CellThresh & (!is.na(Area))
  subimages <- subimages[idx]
  Area <- Area[idx]
  Cell <- Cell[idx]
  blockSlice <- BlockSlice[match(subimages, BlockSlice$block),]
  blockSlice <- data.frame(blockSlice, Area=Area, Cell=Cell)
  }
  if (ifRemoveWhiteColumns){
  # remove gaps between slides along the x-axis
    blockSlice1 <- blockSlice[match(subimages0, blockSlice$block),]    
    xGap <- as.numeric(names(table(sort(BlockSlice[,5]))))
    xgap <- as.numeric(names(table(sort(blockSlice1[,5]))))
    xgap.list <- xGap[sort.list(xgap)]
    blockSlice[,5] <- sapply(1:length(blockSlice[,5]), function(x)
                          blockSlice[x,5] <- xgap.list[xgap==blockSlice[x,5]]) 

  # remove gaps between slides along the y-axis
    yGap <- as.numeric(names(table(sort(BlockSlice[,6]))))
    ygap <- as.numeric(names(table(sort(blockSlice[,6]))))
    ygap.list <- yGap[sort.list(ygap)]
    blockSlice[,6] <- sapply(1:length(blockSlice[,6]), function(x)
                             blockSlice[x,6] <- ygap.list[ygap== blockSlice[x,6]]) 
  }

# YY edits 20/10/2014: account for samples scanned at 40X
# YY edits start
  finalScanInFile = file.path(paste(imgDir,  ff, sep=''), "FinalScan.ini")
  temp = scan(finalScanInFile, what = "character", sep = "\n")
  is40x <- any(grepl('OriginalAppMag = 40', temp))
  if (is40x){
      size0 <- round(size0/2)
      }
# YY edits end

  
# define window size
  minX <- 0
  maxX <- max(blockSlice[,5])
  minY <- 0
  maxY <- max(blockSlice[,6])
  
# main body
  CellPos <- NULL
  Mask <- matrix(0, size0[1]/W, size0[2]/W)  
  colorImage <- array(1, dim=c(nrow(Mask), ncol(Mask), 3))
  for (subimage in subimages){
    xPosition = as.numeric(blockSlice[as.character(blockSlice$block) == subimage, 5])
    yPosition = as.numeric(blockSlice[as.character(blockSlice$block) == subimage, 6])
    cBlockXP = xPosition/4
    cBlockYP = yPosition/4

    if (subimage %in% subimages0){
      cellPos <- try(read.table(paste(classifiedImgDir, ff,'/Cells/', subimage, '.txt', sep=''), header=T))
      if (class(cellPos)!='try-error')
      if (ncol(cellPos)==105){
        if (ifGetCellMor){
          CellPos <- rbind(CellPos, cbind(as.character(cellPos[,2]), floor((cellPos[,3]+cBlockXP)/W), floor((cellPos[,4]+cBlockYP)/W), cellPos[,5:ncol(cellPos)]))
        }else{
          CellPos <- rbind(CellPos, cbind(as.character(cellPos[,2]), floor((cellPos[,3]+cBlockXP)/W), floor((cellPos[,4]+cBlockYP)/W)))
        }
        }}
    img <- readImage(paste(imgDir, ff, '/', subimage, '.jpg', sep=''))
    if(nrow(img)>10 & ncol(img)>10 & length(dim(img)) == 3){
      mask <- !(img[, , 1] > 0.85 & img[, , 2] > 0.85 & img[, , 3] > 0.85) 
      #mask.resize <- apply(array(mask*1, c(W,nrow(mask)/W,W,ncol(mask)/W)), c(2,4), max) @changed by YY 21/20/14
      mask.resize <- resize(mask, nrow(mask)/W, ncol(mask)/W)
      img.resize <- resize(img, nrow(img)/W, ncol(img)/W)

      # YY 21/20/14: deal with rounding errors at the edge of the image
      x1 <- cBlockXP/W+1
      x2 <- cBlockXP/W+nrow(mask)/W
      y1 <- 1+cBlockYP/W
      y2 <- cBlockYP/W+ncol(mask)/W
      if( (y2-y1 > 2) & (x2-x1 > 2) ){
          if (x2>nrow(Mask)){
          newx <- nrow(Mask) - x1 +1
          mask.resize <- mask.resize[1:newx,]
          img.resize <- img.resize[1:newx,,]
          x2 <- nrow(Mask)
      }
      if(y2>ncol(Mask)){
          newy <- ncol(Mask) - y1 +1
          mask.resize <- mask.resize[,1:newy]
          img.resize <- img.resize[,1:newy,]
          y2 <- ncol(Mask)
      }
      Mask[x1:x2, y1:y2] <- mask.resize
      colorImage[x1:x2, y1:y2,1] <- img.resize[,,1]
       #apply(array(img[,,1], c(W,nrow(mask)/W,W,ncol(mask)/W)), c(2,4), mean) @changed by YY 21/20/14          
      colorImage[x1:x2, y1:y2,2] <- img.resize[,,2]
        #apply(array(img[,,2], c(W,nrow(mask)/W,W,ncol(mask)/W)), c(2,4), mean)         
      colorImage[x1:x2, y1:y2,3] <- img.resize[,,3]
        #apply(array(img[,,3], c(W,nrow(mask)/W,W,ncol(mask)/W)), c(2,4), mean)
  } }}
  colorImage <- rgbImage(colorImage[,,1], colorImage[,,2], colorImage[,,3])
  writeImage(colorImage, file=paste('./OutputImage/', ff, 'OriginalImage.jpg', sep=''), quality=60)

  CellPos <- CellPos[!duplicated(CellPos),]
  if (ifGetCellMor){
    CellPos <- data.frame(class=CellPos[,1], x=as.numeric(CellPos[,2]), y=as.numeric(CellPos[,3]), CellPos[,4:ncol(CellPos)], stringsAsFactors=FALSE)
  }else{
    CellPos <- data.frame(class=CellPos[,1], x=as.numeric(CellPos[,2]), y=as.numeric(CellPos[,3]), stringsAsFactors=FALSE)
  }
  CellPos$x[CellPos$x==0] <- 1
  CellPos$y[CellPos$y==0] <- 1
  save(CellPos, Mask, blockSlice, file=paste('./CellPosAndMask/', ff, '.rdata', sep=''))
}

plotImageDensity <- function(ff, h=5, w=5, plotKernelCellType=1:3, plotDensityAndMask=FALSE,cell.cex=.5, cellTypeExclude='a', cellColors=c('green', 'blue', 'red'),
...){
  require(EBImage)
  require(splancs)

  res <- try(load(paste('./CellPosAndMask/', ff, '.rdata', sep='')))
  if (class(res)!='try-error'){
  
    width <- nrow(Mask)*w
    height <- ncol(Mask)*w 
    CellPos <- CellPos[!CellPos[,1] %in% cellTypeExclude, ]
    cl <- as.character(CellPos[,1])
    x <- as.numeric(CellPos[,2])
    y <- as.numeric(CellPos[,3])
    y <- ncol(Mask) - y +1
    cellCol <- replace.vector(cl, c('c','l','o'), cellColors)
    
    jpeg(paste('./OutputImage/',ff,'Mask.jpg', sep=''), width=width, heigh=height)
    par(mar=c(0,0,0,0))
    image(Mask[,ncol(Mask):1], col=c('white', 'mistyrose'), xaxt='n', yaxt='n', ...)
    points(x/nrow(Mask), y/ncol(Mask), pch=19, cex=cell.cex, col=cellCol, ...)
    dev.off()
      
    cells <- c('c', 'o', 'l')
    cellTypes <- c('Cancer', 'Fibroblast', 'Lymphocyte')
    cell.col <- list(Cancer=colorRampPalette(c('white', 'lightgreen', 'green', 'darkgreen', 'darkgreen'))(30),
                     Fibroblast=colorRampPalette(c('white', 'pink', '#FF0DCC'))(20),
                     Lymphocyte=colorRampPalette(c('white', 'darkblue'))(20))

    for (nCellType in plotKernelCellType){
      dat <- list(x=x[cl==cells[nCellType]], y=y[cl==cells[nCellType]]) 
      res <- kernel2d(as.points(dat), poly=cbind(c(min(dat$x), min(dat$x), max(dat$x), max(dat$x)), c(min(dat$y), max(dat$y), max(dat$y), min(dat$y))), h0=h, nx=min(length(dat$x), nrow(Mask)), ny=min(length(dat$y), ncol(Mask)))
      
      jpeg(paste('./OutputImage/', ff, 'KernelDensity', cellTypes[nCellType], '.jpg', sep=''), width=width, height=height, quality=50)
      par(mar=c(0,0,0,0))
      image(res, add=F, ylim=c(0,ncol(Mask)), xlim=c(0,nrow(Mask)), col=cell.col[[nCellType]][1:min(length(cell.col[[nCellType]]), max(res$z)*100)], xaxt='n', yaxt='n')
      points((x)[cl!=cells[nCellType]], (y)[cl!=cells[nCellType]], pch=19, cex=cell.cex, col=cellCol[cl!=cells[nCellType]], ...)
     dev.off()

      #png(paste('./OutputImage/', ff,'CellPosAndMask', cellTypes[nCellType], '.png', sep=''), width=width, heigh=height)
      #par(mar=c(0,0,0,0))
      #image(Mask[,ncol(Mask):1], col=c('white', 'grey'), xaxt='n', yaxt='n')
      #points(dat$x/nrow(Mask), dat$y/ncol(Mask), pch='.')
      #dev.off()
      if(plotDensityAndMask){
      densityImage <- readImage(paste('./OutputImage/', ff, 'KernelDensity', cellTypes[nCellType],'.png', sep=''))
      maskImage <- readImage(paste('./OutputImage/', ff, 'Mask.png', sep=''))
      
      rImage=densityImage[,,1]
      gImage=densityImage[,,2]
      bImage=densityImage[,,3]

      rImage[rImage>.99 & maskImage[,,1]<1]=.85
      gImage[gImage>.99 & maskImage[,,1]<1]=.85
      bImage[bImage>.99 & maskImage[,,1]<1]=.85
    
      rgbMask=rgbImage(red=rImage, green=gImage, blue=bImage)
      writeImage(rgbMask, file=paste('./OutputImage/', ff, 'DensityAndMask', cellTypes[nCellType],'.png', sep=''))
    }}
  }
}


