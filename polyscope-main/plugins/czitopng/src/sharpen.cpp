#include <string> 
#include <opencv2/opencv.hpp>

using namespace std;
using namespace cv;

int main(int argc, char** argv) {
    if (argc == 1) {
        printf("Please provide a path to the image to be sharpened.\n");
        return 1;
    }
    
    int i;
    string inFilename;
    string outFilename;
    int radius = 5;
    int amount = 2;
    
    for (i=0; i<argc-2; i++) {
        if (strcmp(argv[i], "-r") == 0) {
            radius = stoi(argv[i+1]);
            i=i+1;
        } else if (strcmp(argv[i], "-a") == 0) {
            amount = stoi(argv[i+1]);
            i=i+1;
        }
    }
    
    if (i==argc-2) {
        inFilename = string(argv[argc-2]);
        outFilename = string(argv[argc-1]);
    } else {
        inFilename = string(argv[argc-1]);
        outFilename = string(argv[argc-1]);
    }
    
    Mat image;
    vector<Mat> labChannels;
    Mat sharpL;

    image = imread(inFilename, CV_LOAD_IMAGE_COLOR);
    cvtColor(image, image, COLOR_BGR2Lab);
    split(image, labChannels);
    GaussianBlur(labChannels[0], sharpL, Size(0, 0), radius);
    addWeighted(labChannels[0], amount+1, sharpL, -amount, 0, labChannels[0]);
    merge(labChannels, image);
    cvtColor(image, image, COLOR_Lab2BGR);
    imwrite(outFilename, image);
    
    return 0;
}
