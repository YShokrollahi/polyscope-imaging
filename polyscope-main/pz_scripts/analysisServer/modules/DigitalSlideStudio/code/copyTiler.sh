#BSUB -J "tile"
#BSUB -o z.output.%J
#BSUB -e z.errors.%J
#BSUB -n 4
#BSUB -P DMPYXYAAB
#BSUB -q short
#BSUB -B
export TMPDIR=`pwd`
bash _daTile.sh
