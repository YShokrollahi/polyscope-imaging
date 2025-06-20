#oldcrimage<-1
library(CRImage)
library(EBImage)

args <- commandArgs(trailingOnly = TRUE)
workingDirectory <- as.character(args[1])
outputDir <- paste(workingDirectory,'/../result/',sep='')
dir.create(outputDir)
currentDir<-workingDirectory
setwd(outputDir)
source(paste(currentDir,'/functions.R',sep=''))
source(paste(currentDir,'/CRImage_modifications.R',sep=''))

# # # # # # # # # # # # # # # # # # # 
# 1.1 Running CRImage directly
# # # # # # # # # # # # # # # # # # #
classifierName <- paste(currentDir,'/classifier_3.3_and_Da2',sep='')
load(classifierName)
classifier <- model

imgDir <- paste(currentDir,'/../data/cws/',sep='')

#InDir<-paste('^',as.character(args[1]),'$',sep='')
ffs <- dir(imgDir)
ffs <- ffs[!grepl('.csv', ffs) & !grepl('.sh', ffs)  & !grepl('.png', ffs)]
classifier <- model
load(paste(currentDir,'/pixelClassifier',sep=''))
pixelClassifier <- model
load(paste(currentDir,'/../code/penClassifier',sep=''))
load(paste(currentDir,'/../code/referenceHist',sep=''))

for (ff in ffs){
    output <- processAperio(classifier=classifier, 
                        inputFolder=paste(imgDir,ff,sep=''),
                        outputFolder=paste(outputDir,ff,sep=''),
                        greyscaleImage=1,
                        identifier='Da[0-9]+', 
                        numSlides=1,   
                        cancerIdentifier='c',
                        maxShape=800,
                        minShape=40,
                        failureRegion=2000,
                        slideToProcess=NA, 
                        KS=TRUE,
                        colors=c("white", "green", "blue", "red"),
                        classesToExclude=c('a', 'l'),
                        threshold="otsu",
                        ksToExclude=c('l'),
                        pixelClassifier=NA,
                        classifyStructures=FALSE,
                        densityToExclude=c('a'),## new
                        numDensityWindows=16,## new
                        resizeFactor=2,## new
                        plotCellTypeDensity=TRUE #   # new
                        )
 gc()

}


# get cell position
for (ff in ffs){
  getCellPos(ff, imgDir, outputDir)
  plotImageDensity(ff, plotKernelCellType=1)
  plotImageDensity(ff, plotKernelCellType=2)
}
