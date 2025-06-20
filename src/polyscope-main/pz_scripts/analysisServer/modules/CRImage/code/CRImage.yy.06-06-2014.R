TARGETDIR<-paste(getwd(),'/../result',sep='')
print(paste('TargetDir=',TARGETDIR))
dir.create(TARGETDIR)
setwd(paste(getwd(),'/../result',sep=''))
oldcrimage=TRUE
source('functions.R')



ffs <- dir('/media/Projects/METABRIC/result/CellPosAndMask')
ffs <- sapply(ffs, function(x) strsplit(x, split='.r', fixed=T)[[1]][1])
load('../data/traitAllMatchedCNGEImg1026Samples.rdata')
ffs <- setdiff(trait$file, ffs)

orgImgDir <- '/media/Projects/METABRIC/data/cws/'
claImgDir <- '/media/Projects/METABRIC/result/CRImage/'


for (ff in  ffs){
  try(getCellPos(ff, orgImgDir, claImgDir, W=10, ifGetCellMor=TRUE))
}
