# Pages > Index

!!! abstract
    Index page offers a unified interface for file management, slide processing job submission, and status display, and it connects to result pages.

## Polyscope Storage

Polyscope offers dedicated file storage space for each user so they can upload files to be processed. Polyscope is not meant for permenant storage. Currently, there is no storage quota. We recommend following file management etiquettes below:

* Processed files can be safetly deleted. 
* Perform routine clean up of old files to leave storage space for other users. 
* Refrain from using Polyscope as the primary data storage. 
* Follow consistent file naming conventions and folder organization.

For developer, user folder is located at `/rsrch9/home/plm/polyscope/media/Users/`.

## Files Tab

Files Tab is the default view after [logging in](pages_login.md). 

### Upload files

Viewing slides on Polyscope starts with file uploading. Polyscope supported file formats include: 

* pathology slide formats (`svs`, `ndpi`, `czi`, `scn`, `mrxs`, `vsi`, `vms`);
* medical imaging formats (`dcm`, `nii`, `nrrd`);
* common image formats (`jpg` or `jpeg`, `png`, `tif` or `tiff`, `bmp`, `gif`, `webp`, `jp2`);
* other formats (`pdf`).

Multiple files can be uploaded concurrently, with a combined maximum file size limit of **5 GB**.

To upload files, you can: 

1. click the **Upload Files** button;
2. drag files to the file drop area on the page;
3. click anywhere on the file drop area. 

A progress bar will be displayed on top of the page for all files being uploaded.  

!!! tip
    When a file is uploading, you cannot upload a new file. However, you can select multiple files to be uploaded at the same time.

!!! note
    File and folder names containing space (` `) will be modified so underscores (`_`) are used instead of space characters.

!!! warning "Known issue"
    Uploading files with the same name as existing files may result overwritten of the old files. 

!!! warning
    Do NOT close the browser tab/window or refresh the page while uploading, otherwise uploading will be disrupted and you need to initiate new uploading. 

### Manage files

After files are uploaded, you can perform file management operations. To organize files, you can create a new folder through the **New Folder** button. To rename a file or a folder, you can click on the **pen** icon when hovering on the file or folder, or you can select multiple files and/or folders through the checkboxes and click the **Rename** button. 

For deletion, select the file(s) and/or folder(s) using the checkboxes and click the **Delete** button. A popup dialog will shown to confirm the deletion action. 

!!! warning
    File deletion cannot be undone by user. However, in rare cases, please contact us for file recovery.

Currently, moving (cut-and-paste) and duplicate file or folder is not supported. 

### Process files

Uploaded files of various formats need to be converted to internal and standardized format (Deep Zoom File, DZI) so Polyscope can offer easy and responsive access to the images through a web browser. To process uploaded files, select the file(s) using the checkboxes and click the **Process to DZI** button. Currently, batch process files by selecting a folder is not supported but you can navigate into the folder to select all/multiple files within. A job will be created for each file to parse and convert the image format. The page will automatically transition to the [**Processing Tab**](#processing-tab) once a processing job is submitted.

## Processing Tab

The **Processing Tab** displays the queue of jobs to be processed and their status. You will see the job status going from checksum, pending, upload, queue, processing, all the way to finished. Normally, processing of a file takes somewhere around 1 to 10 minumtes. As all users share the same processing queue, during peak usage or batch submission of many jobs, jobs may be queued. After the job is finished, "finished" status will be displayed, and click **view results** button to go to the processed image. Otherwise, navigate to [**My Results**](pages_results.md) to view the processed files. 
