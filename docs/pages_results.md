# Pages > My Results

!!! abstract
    My Results page is the place to manage all processed images and create image views.

## Manage Processed Images

From the **Index page**, click the **My Results** button in the navigation bar to go to the **My Results** page. You have access only to your own user directory under `.../customers/<your_email>` where `<your_email>` is your email address/username where `@` and `.` is replaced by `-`.

!!! example
    For `jsmith1@mdanderson.org`, the user page is located at `.../customers/jsmith1-mdanderson-org/`.

![User page](img/user-page.png "User page")

Use the buttons to the left to organize the processed files.  

* :octicons-trash-24:{ title="Delete zooms" }: delete selected file(s);
* :material-folder-plus:{ title="Create new folder" }: create a new folder;
* :fontawesome-solid-up-down:{ title="Sort samples" }: sort file list;
* :material-filter:: To be removed;
* :material-set-merge:{ title="Create multizoomer" }: create Multizoomer from multiple images.

To move file(s) to a folder, either drag the selected files to the destination folder, or use context menu of the image thumbnails to 'copy' or 'cut' and then 'paste'. 

Operations like deletion and Multizoomer creation requires selection of multiple files: click on the image thumbnail to select, which will highlight the selection in yellow; click again to deselect. 

Finally, the slider allows you to adjust the thumbnail size.

Currently, file name renaming of processed files and their folders is not supported. 

## Create Multizoomer Image View Page

A 'Polyzoomer' view may contain one image/file, or more than one, which is called 'Multizoomer'. In a 'Multizoomer' view, the display areas of each file are synchronized across user's pan and zoom actions. To create a Multizoomer view, drag one or more files to the right side of the user page. When all files are added, click the 'multizoom' button on the left to create the view. This will be added to the list of files on the left. Double click on the thumbnail of a 'Polyzoomer' or 'Multizoomer' to open them in the [**Image Page**](pages_image.md).

!!! note
    Changes (annotation edits) to the underlying polyzoomer files will propogate to the dependent Multizoomer views.

![User page while creating Multizoomer view](img/multizoom.png "User page while creating Multizoomer view")
