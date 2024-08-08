# UI Styles

This module allows:
- developers to define styles (simple lists of concurrent CSS classes) from
  modules and themes
- site builders to use those styles on:
    - blocks from Block layout with [UI Styles Block](./modules/ui_styles_block)
    - formatted text with [UI Styles CKEditor 5](./modules/ui_styles_ckeditor5)
    - unpublished content entities with [UI Styles Entity Status](./modules/ui_styles_entity_status)
    - blocks and section from Layout Builder with
      [UI Styles Layout Builder](./modules/ui_styles_layout_builder)
    - theme regions with [UI Styles Page](./modules/ui_styles_page)
    - style format, pager and exposed form from Views with
      [UI Styles Views](./modules/ui_styles_views)
- to browse styles from a library page with
  [UI Styles Library](./modules/ui_styles_library)

**Example of a plugin declaration in the YAML file**

```yaml
colors_background_color:
  category: Background
  label: Background color
  description: Similar to the contextual text color classes, easily set the background of an element to any contextual class.
  options:
    bg-primary: Primary
    bg-secondary: Secondary
    bg-success: Success
    bg-danger: Danger
    bg-warning: Warning
    bg-info: Info
    bg-light: Light
    bg-dark: Dark
    bg-white: White
    bg-transparent: Transparent
  previewed_with:
    - border
    - p-2
```

Where:
- `colors_background_color` is the plugin ID
- 'label' is used in the library and the layout builder forms
- 'description' is only used in the library
- 'category' (optional) to group styles in the forms
- 'options' is the list of all classes and labels of the style
- 'previewed_with' (optional) is a list of classes used in the library to pimp the preview
- 'previewed_as' (optional) can be:
    - `inside`: default
    - `aside`: for styles not intended to be applied on `p` tag and having side effects
    - `hidden`: when the style may have side effects on the whole styles library

You can disable a plugin by declaring a plugin with the same ID and if your
module has a higher weight than the module declaring the plugin, example:

```yaml
colors_background_color:
  enabled: false
```


## Requirements

This module requires no modules outside of Drupal core.

This module requires the
[PHP CSS Parser](https://github.com/MyIntervals/PHP-CSS-Parser) library, so it
requires to be installed with Composer.

A patch may also be required on this library, it is documented into the
`composer.json` file of the module.

If you are using `cweagans/composer-patches` with the `enable-patching` option
enabled in your `composer.json`, the patch should be applied automatically.


## Installation

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).


## Configuration

The module has no menu or modifiable settings. There is no configuration.

The submodules provide new configuration options depending on the submodule
specificities.
