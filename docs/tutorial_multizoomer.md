# Tutorial 3: Create and View a Multizoomer

This tutorial covers the creation and viewing of a Multizoomer, synchronized viewing of multiple slide views. For more detailed information, refer to the [Documentation](pages.md).

## Introduction
Multizoomer is a powerful tool that enables synchronized viewing of multiple images. It is particularly useful for:

- Displaying slide annotations from multiple sources.
- Viewing multiple imaging channels simultaneously.
- Comparing different imaging modalities of adjacent tissue.
- Visualizing AI model outputs alongside original images.

A Multizoomer is composed of multiple Polyzoomers. Importantly, it does not duplicate the underlying Polyzoomers; this means that any annotation changes propagate bidirectionally, ensuring data consistency. However, if a Polyzoomer is deleted, a blank view will appear in its place within the Multizoomer.

## Creating a Multizoomer
To create a Multizoomer, follow these steps:

1. Drag individual Polyzoomers onto the right side of the interface to configure the Multizoomer layout.
   * It is recommended to use a maximum of **two columns** for better organization.
   * While the system supports an unlimited number of views, keeping it to around **six views** ensures clarity and usability.
2. Click the **Create Multizoomer** button to finalize the layout. This action will navigate to the newly created Multizoomer page.
3. Click the **α (alpha)** button to designate one Polyzoomer as the reference Polyzoomer. The reference Polyzoomer controls synchronized actions across all views (explained in the next section).

## Multizoomer Interaction
Once a Multizoomer is created, interactions are synchronized based on the reference Polyzoomer:

- **Pan and zoom movements** applied to the reference Polyzoomer will be mirrored across all other Polyzoomers in the Multizoomer.
- **Other operations** will function as they do on an individual Polyzoomer.

## Sharing a Multizoomer
Sharing a Multizoomer is straightforward:

- The URL of the Multizoomer page serves as the shareable link. Example: `
  https://polyscope.mdanderson.org/customers/jsmith1-mdanderson-org/multizooms/Path000009_202502041751/page/INDEX/
  `
- The link is publicly accessible, so it is advisable to keep it confidential.
- It can be accessed both inside and outside MD Anderson without requiring a VPN.
- Since a Multizoomer depends on its source Polyzoomers, consider creating multiple Polyzoomers from a single slide if you need to share them with different individuals independently.

By following these steps, you can efficiently create, interact with, and share Multizoomers for various imaging and annotation tasks.

