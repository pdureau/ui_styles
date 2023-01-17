# UI Styles

## Overview

This module allows:

- developers to define styles (simple lists of concurrent CSS classes) from modules and themes
- site builders to use those styles on blocks and section from Layout Builder (with `ui_styles_layout_builder` sub-module)
- site builders to use those styles on style format, pager and exposed form from Views (with `ui_styles_views` sub-module)
- everyone to browse styles from a library page (with `ui_styles_library` sub-module)

## Features

Heavily inspired by [layout\_builder\_styles](https://www.drupal.org/project/layout_builder_styles) module, with those differences:

* styles are plugin-based instead of being config-entity-based, to be created and managed by the themer (inside a YML file), and then only used by the site builder in the layout builder interface (see also: [#3107972](https://www.drupal.org/project/layout_builder_styles/issues/3107972))
* styles are organized as groups of classes, because some styles should only allow one class to be chosen (see also: [#3075502](https://www.drupal.org/project/layout_builder_styles/issues/3075502))
* with a preview page, like [ui_patterns_library](https://ui-patterns.readthedocs.io/en/8.x-1.x/content/patterns-definition.html), available there: /styles
* on rendering, classes are attached to the first render array inside the block, instead of being attached to the block wrapper
* it is possible to add free extra classes, using a simple text field

## Example of a plugin declaration in the YML file

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

* `colors_background_color` is the plugin ID
* 'label' is used in the library and the layout builder forms
* 'description' is only used in the library
* 'category' (optional) to group styles in the forms
* 'options' is the list of all classes and labels of the style
* 'previewed_with' (optional) is a list of classes used in the library to pimp the preview
* 'previewed_as' (optional) can be:
  * `inside`: default
  * `aside`: for styles not intended to be applied on `p` tag and having side effects
  * `hidden`: when the style may have side effects on the whole styles library

You can disable a plugin by declaring a plugin with the same ID and if your
module has a higher weight than the module declaring the plugin, example:

```yaml
colors_background_color:
  enabled: false
```

## Requirements

This module has no specific requirement.

## Recommended Module

[ui\_suite\_bootstrap](https://www.drupal.org/project/ui_suite_bootstrap) is an example of a site-building friendly Drupal theme using [UI Styles](https://www.drupal.org/project/ui_styles) with [UI Patterns](https://www.drupal.org/project/ui_patterns), [Layout Options](https://www.drupal.org/project/layout_options) and [UI Examples](https://www.drupal.org/project/ui_examples) modules, to implements [Bootstrap](https://getbootstrap.com/) 4:

![Overview](doc/schema.png)

## Installation

Install and enable this module like any other Drupal module.

## Configuration

The module has no modifiable settings.
