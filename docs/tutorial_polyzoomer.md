# Annotate using Polyscope

This tutorial explains how to create and view a Polyzoomer, a single slide view, and how to annotate slides using Polyscope. For more detailed information, refer to the [Documentation](pages.md).

## Access Polyscope’s Polyzoomer Page

The URL of a Polyzoomer page serves as its shareable link, for example: `https://polyscope.mdanderson.org/customers/jsmith1-mdanderson-org/Path000010_202502182331/page/test001/`. This link is accessible to everyone, so it is important to keep it confidential.

Polyscope is available both within and outside MD Anderson without the need for a VPN. A desktop browser is recommended for the best experience, but mobile devices with touchscreens are also supported. While co-editing is possible, it is recommended to use only one tab per link to avoid conflicts.

## Create Annotations and Their Types

Before starting an annotation task, consult the annotation requester to determine the appropriate annotation type. Since Polyscope does not save color code descriptions, it is advised to record the color coding before beginning annotation work.

To set a color, click the ‘color picker’ tool. Click on an annotation type icon to enter annotation mode; clicking the icon again will exit this mode. 

- **Point annotation**: Each left-click creates one annotation.
- **Freehand drawing**: Left-click and drag to draw.
- **Rectangle annotation**: Left-click to define one corner, then drag and release to set the opposite corner.

The color can be changed during annotation mode. For additional annotation types, refer to the [Documentation](pages.md#annotation-controls).

## Edit and Delete Annotations

Annotations cannot be moved or resized after creation. If changes are needed, the annotation must be deleted and recreated. To delete an annotation, click on it and follow the prompt. Multiple annotations must be deleted one by one.

Deletion cannot be undone by the user. However, deleted annotations are recorded and can be retrieved for later analysis if necessary.

## Review Annotations

Basic interactions within the annotation viewer include:

- **Pan**: Use mouse drag, arrow keys, or `WASD` keys.
- **Zoom**: Use the mouse scroll wheel or the `-` and `=` (`+`) keys.
- **Full screen**: Click the full-screen icon for an expanded view.

Annotation statistics are displayed in the top-right panel. Annotations should refresh automatically, but if they do not, try refreshing the page or reopening the tab. For more details on view controls, refer to the [Documentation](pages.md#view-controls).

## Download Annotations

Click the ‘Download’ button to save annotations as a `.txt` file. Polyscope uses a proprietary annotation format, where each row represents one annotation. Deleted annotations are also recorded.

Since the annotation file does not contain corresponding slide information, it is recommended to rename the file immediately after downloading. Annotations can be downloaded at any time and multiple times if needed. For more details on the annotation format, refer to the [Documentation](pages.md#download-annotations).
