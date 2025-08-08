# Tutorial 2: Annotate using Polyscope

!!! abstract
    This tutorial explains how to create and view a Polyzoomer, a single image view, and how to annotate images using Polyscope.

## Access Polyscope Polyzoomer Page

The URL of a Polyzoomer page serves as its [shareable](pages_image.md#link-sharing) link, for example: `https://polyscope.mdanderson.org/customers/jsmith1-mdanderson-org/Path000010_202502182331/page/test001/`. This link is accessible to everyone, so it is important to keep it confidential.

Polyscope is available both within and outside MD Anderson without the need for a VPN. A desktop browser is recommended for the best experience, but mobile devices with touchscreens are also supported. While co-editing is possible, it is recommended to use only one tab per link to avoid conflicts.

## Create Annotations and Their Types

Before starting an annotation task, consult the annotation requester to determine the appropriate annotation type. Since Polyscope does not save color code descriptions, it is advised to record the color coding before beginning annotation work.

To set a color, click the :material-eyedropper:{ title="Select color" } tool. Click on an annotation type icon to enter annotation mode; clicking the icon again will exit this mode. Popular [annotation types](pages_image.md#create-annotations) are: 

- `.`{ title="Draw point" } **Point annotation**: Each left-click creates one annotation.
- :material-pen:{ title="Draw freehand polygon" }**Freehand drawing**: Left-click and drag to draw.
- :material-crop-square:{ title="Draw rectangle" }**Rectangle annotation**: Left-click to define one corner, then drag and release to set the opposite corner.

The color can be changed during annotation mode.

## Edit and Delete Annotations

Annotations cannot be moved or resized after creation. If changes are needed, the annotation must be [deleted](pages_image.md#delete-annotations) and recreated. To delete an annotation, click on it and follow the prompt. Multiple annotations must be deleted one by one.

## Review Annotations

You can use the mouse and the keyboard to [explore](pages_image.md#view-controls) the images at different locations and zoom levels to review annotations. Here are the common interactions:

- **Pan**: Mouse left click to drag or press ++arrow-up++, ++arrow-down++, ++arrow-left++, or ++arrow-right++ keys.
- **Zoom**: Use the mouse scroll wheel or press ++equal++ or ++minus++  keys.
- **Full screen**: Click :material-fullscreen: to enter full screen view.

Annotation [statistics](pages_image.md#annotation-statistics) are displayed in the side panel. Annotations should refresh automatically, but if they do not, try refreshing the page or reopening the tab.

## Download Annotations

Click the [:octicons-download-24:Export](pages_image.md#download-annotations) button to save annotations as a `.txt` file. Polyscope uses a proprietary [annotation format](pages_image.md#polyscope-annotation-txt-format), where each row represents one annotation. 

Since the annotation file does not contain corresponding slide information, it is recommended to rename the file immediately after downloading. Annotations can be downloaded at any time and multiple times if needed.
