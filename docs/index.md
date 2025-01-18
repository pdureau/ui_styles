# Introduction

!!! info "UI Suite"

    [UI Styles](https://www.drupal.org/project/ui_styles) is part of
    [UI Suite](https://www.drupal.org/project/ui_suite).

Design systems generally provide helpers and utilities CSS classes to apply on
any element to handle generic CSS effects like background color, text effects,
spacing, etc.

UI Styles allows:

* developers to define styles (simple lists of concurrent CSS classes) from
modules and themes in `my_theme.ui_styles.yml` files
* site builders to use those styles depending on the modules enabled (
submodules of UI Styles or modules of its ecosystem).

The module generates a styles library page to be used as documentation for
content editors or as a showcase for business and clients.


## Example usage

Example of plugin declarations in the YML files.


### Simple example

```yaml
colors_background_color:
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
* `label` is used in the library and the layout builder forms
* `description` is only used in the library
* `options` is the list of all classes and labels of the style
* `previewed_with` is a list of classes used in the library to pimp the preview


### Example with previewed_with per option

```yaml
text_color:
  label: Text color
  category: Color
  previewed_with:
    - fr-p-1v
  options:
    fr-text-action-high--blue-france: Action-high blue France
    fr-text-inverted--blue-france:
      label: Inverted blue France
      previewed_with:
        - fr-background-action-high--blue-france
```

For the option `fr-text-inverted--blue-france` the preview classes `fr-p-1v` and
`fr-background-action-high--blue-france`  will be cumulated.


### Example with description per option

```yaml
typography:
  label: Typography
  description: "Material Design's text sizes and styles were developed to balance content density and reading comfort under typical usage conditions. https://m2.material.io/develop/web/components/typography"
  options:
    mdc-typography--headline1:
      label: Headline 1.
      description: The largest text on the screen, reserved for short, important text or numerals.
    mdc-typography--headline2: Headline 2
    mdc-typography--headline3: Headline 3
    mdc-typography--headline4: Headline 4
    mdc-typography--headline5: Headline 5
    mdc-typography--headline6: Headline 6
```

It is also possible to specify a description on an option.


## Best practices


### Options are mutually exclusive

When declaring the styles, if you can combine many values of a style, that means
it is different styles.

The goal is to have each option of one specific style plugin to be mutually
exclusive.

!!! success "Do"

    ```yaml
    borders_border:
    category: "Borders"
    label: "Border"
    options:
      border: "Additive All"
      border-top: "Additive Top"
      border-end: "Additive End"
      border-bottom: "Additive Bottom"
      border-start: "Additive Start"

    borders_border_subtractive:
    category: "Borders"
    label: "Border subtractive"
    options:
      border-0: "All"
      border-top-0: "Top"
      border-end-0: "End"
      border-bottom-0: "Bottom"
      border-start-0: "Start"
    ```

!!! failure "Don't"

    ```yaml
    borders_border:
    category: "Borders"
    label: "Border"
    options:
      border: "Additive All"
      border-top: "Additive Top"
      border-end: "Additive End"
      border-bottom: "Additive Bottom"
      border-start: "Additive Start"
      border-0: "Subtractive All"
      border-top-0: "Subtractive Top"
      border-end-0: "Subtractive End"
      border-bottom-0: "Subtractive Bottom"
      border-start-0: "Subtractive Start"
    ```


## Should I do a component or a style?


### If the CSS class is only for layout purpose

* Defines props in the [component](https://www.drupal.org/project/ui_patterns)
  that will be used as layout.


### If the CSS class is "standalone"

* Create a style


### If the CSS class needs specific HTML markup

* It should go into the related [component](https://www.drupal.org/project/ui_patterns).
