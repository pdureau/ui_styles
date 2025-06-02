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


## Managing plugin form rendering

Styles can be rendered with different form elements in the interface depending
on the style declaration in the YML file.

There are three different source plugins available by default to manage the
rendering of the form element:
* On/Off checkbox: Automatically applied when there is only one option defined
* Toolbar: Automatically applied when there are several options with icons
  associated to all the options
* Select: Always applicable


### Example rendering an On/Off checkbox

```yaml
clearfix:
  category: "Float"
  label: "Clearfix"
  description: "Easily clear floats by adding <code>.clearfix</code> to the parent element."
  options:
    clearfix: "Clearfix"
```

There is only one option, so the plugin source On/Off checkbox will be used to
render the style form element in the UI.


### Example rendering a Toolbar

```yaml
borders_border:
  category: "Borders"
  label: "Border"
  description: "Use it to add an element's borders. Choose from all borders or one at a time."
  options:
    border:
      label: "Additive All"
      icon: "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgZmlsbD0iY3VycmVudENvbG9yIiBjbGFzcz0iYmkgYmktYm9yZGVyLW91dGVyIiB2aWV3Qm94PSIwIDAgMTYgMTYiPgogIDxwYXRoIGQ9Ik03LjUgMS45MDZ2LjkzOGgxdi0uOTM4em0wIDEuODc1di45MzhoMVYzLjc4aC0xem0wIDEuODc1di45MzhoMXYtLjkzOHpNMS45MDYgOC41aC45Mzh2LTFoLS45Mzh6bTEuODc1IDBoLjkzOHYtMUgzLjc4djF6bTEuODc1IDBoLjkzOHYtMWgtLjkzOHptMi44MTMgMHYtLjAzMUg4LjVWNy41M2gtLjAzMVY3LjVINy41M3YuMDMxSDcuNXYuOTM4aC4wMzFWOC41em0uOTM3IDBoLjkzOHYtMWgtLjkzOHptMS44NzUgMGguOTM4di0xaC0uOTM4em0xLjg3NSAwaC45Mzh2LTFoLS45Mzh6TTcuNSA5LjQwNnYuOTM4aDF2LS45Mzh6bTAgMS44NzV2LjkzOGgxdi0uOTM4em0wIDEuODc1di45MzhoMXYtLjkzOHoiLz4KICA8cGF0aCBkPSJNMCAwdjE2aDE2VjB6bTEgMWgxNHYxNEgxeiIvPgo8L3N2Zz4K"
    border-top:
      label: "Additive Top"
      icon: "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgZmlsbD0iY3VycmVudENvbG9yIiBjbGFzcz0iYmkgYmktYm9yZGVyLXRvcCIgdmlld0JveD0iMCAwIDE2IDE2Ij4KICA8cGF0aCBkPSJNMCAwdjFoMTZWMHptMSAyLjg0NHYtLjkzOEgwdi45Mzh6bTYuNS0uOTM4di45MzhoMXYtLjkzOHptNy41IDB2LjkzOGgxdi0uOTM4ek0xIDQuNzE5VjMuNzhIMHYuOTM4aDF6bTYuNS0uOTM4di45MzhoMVYzLjc4aC0xem03LjUgMHYuOTM4aDFWMy43OGgtMXpNMSA2LjU5NHYtLjkzOEgwdi45Mzh6bTYuNS0uOTM4di45MzhoMXYtLjkzOHptNy41IDB2LjkzOGgxdi0uOTM4ek0uNSA4LjVoLjQ2OXYtLjAzMUgxVjcuNTNILjk2OVY3LjVILjV2LjAzMUgwdi45MzhoLjV6bTEuNDA2IDBoLjkzOHYtMWgtLjkzOHptMS44NzUgMGguOTM4di0xSDMuNzh2MXptMS44NzUgMGguOTM4di0xaC0uOTM4em0yLjgxMyAwdi0uMDMxSDguNVY3LjUzaC0uMDMxVjcuNUg3LjUzdi4wMzFINy41di45MzhoLjAzMVY4LjV6bS45MzcgMGguOTM4di0xaC0uOTM4em0xLjg3NSAwaC45Mzh2LTFoLS45Mzh6bTEuODc1IDBoLjkzOHYtMWgtLjkzOHptMS44NzUgMGguNDY5di0uMDMxaC41VjcuNTNoLS41VjcuNWgtLjQ2OXYuMDMxSDE1di45MzhoLjAzMXpNMCA5LjQwNnYuOTM4aDF2LS45Mzh6bTcuNSAwdi45MzhoMXYtLjkzOHptOC41LjkzOHYtLjkzOGgtMXYuOTM4em0tMTYgLjkzN3YuOTM4aDF2LS45Mzh6bTcuNSAwdi45MzhoMXYtLjkzOHptOC41LjkzOHYtLjkzOGgtMXYuOTM4em0tMTYgLjkzN3YuOTM4aDF2LS45Mzh6bTcuNSAwdi45MzhoMXYtLjkzOHptOC41LjkzOHYtLjkzOGgtMXYuOTM4ek0wIDE2aC45Njl2LS41SDF2LS40NjlILjk2OVYxNUguNXYuMDMxSDB6bTEuOTA2IDBoLjkzOHYtMWgtLjkzOHptMS44NzUgMGguOTM4di0xSDMuNzh2MXptMS44NzUgMGguOTM4di0xaC0uOTM4em0xLjg3NS0uNXYuNWguOTM4di0uNUg4LjV2LS40NjloLS4wMzFWMTVINy41M3YuMDMxSDcuNXYuNDY5em0xLjg3NS41aC45Mzh2LTFoLS45Mzh6bTEuODc1IDBoLjkzOHYtMWgtLjkzOHptMS44NzUgMGguOTM4di0xaC0uOTM4em0xLjg3NS0uNXYuNUgxNnYtLjk2OWgtLjVWMTVoLS40Njl2LjAzMUgxNXYuNDY5eiIvPgo8L3N2Zz4K"
    border-bottom:
      label: "Additive Bottom"
      icon: "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgZmlsbD0iY3VycmVudENvbG9yIiBjbGFzcz0iYmkgYmktYm9yZGVyLWJvdHRvbSIgdmlld0JveD0iMCAwIDE2IDE2Ij4KICA8cGF0aCBkPSJNLjk2OSAwSDB2Ljk2OWguNVYxaC40NjlWLjk2OUgxVi41SC45Njl6bS45MzcgMWguOTM4VjBoLS45Mzh6bTEuODc1IDBoLjkzOFYwSDMuNzh2MXptMS44NzUgMGguOTM4VjBoLS45Mzh6TTcuNTMxLjk2OVYxaC45MzhWLjk2OUg4LjVWLjVoLS4wMzFWMEg3LjUzdi41SDcuNXYuNDY5ek05LjQwNiAxaC45MzhWMGgtLjkzOHptMS44NzUgMGguOTM4VjBoLS45Mzh6bTEuODc1IDBoLjkzOFYwaC0uOTM4em0xLjg3NSAwaC40NjlWLjk2OWguNVYwaC0uOTY5di41SDE1di40NjloLjAzMXpNMSAyLjg0NHYtLjkzOEgwdi45Mzh6bTYuNS0uOTM4di45MzhoMXYtLjkzOHptNy41IDB2LjkzOGgxdi0uOTM4ek0xIDQuNzE5VjMuNzhIMHYuOTM4aDF6bTYuNS0uOTM4di45MzhoMVYzLjc4aC0xem03LjUgMHYuOTM4aDFWMy43OGgtMXpNMSA2LjU5NHYtLjkzOEgwdi45Mzh6bTYuNS0uOTM4di45MzhoMXYtLjkzOHptNy41IDB2LjkzOGgxdi0uOTM4ek0uNSA4LjVoLjQ2OXYtLjAzMUgxVjcuNTNILjk2OVY3LjVILjV2LjAzMUgwdi45MzhoLjV6bTEuNDA2IDBoLjkzOHYtMWgtLjkzOHptMS44NzUgMGguOTM4di0xSDMuNzh2MXptMS44NzUgMGguOTM4di0xaC0uOTM4em0yLjgxMyAwdi0uMDMxSDguNVY3LjUzaC0uMDMxVjcuNUg3LjUzdi4wMzFINy41di45MzhoLjAzMVY4LjV6bS45MzcgMGguOTM4di0xaC0uOTM4em0xLjg3NSAwaC45Mzh2LTFoLS45Mzh6bTEuODc1IDBoLjkzOHYtMWgtLjkzOHptMS44NzUgMGguNDY5di0uMDMxaC41VjcuNTNoLS41VjcuNWgtLjQ2OXYuMDMxSDE1di45MzhoLjAzMXpNMCA5LjQwNnYuOTM4aDF2LS45Mzh6bTcuNSAwdi45MzhoMXYtLjkzOHptOC41LjkzOHYtLjkzOGgtMXYuOTM4em0tMTYgLjkzN3YuOTM4aDF2LS45Mzh6bTcuNSAwdi45MzhoMXYtLjkzOHptOC41LjkzOHYtLjkzOGgtMXYuOTM4em0tMTYgLjkzN3YuOTM4aDF2LS45Mzh6bTcuNSAwdi45MzhoMXYtLjkzOHptOC41LjkzOHYtLjkzOGgtMXYuOTM4ek0wIDE1aDE2djFIMHoiLz4KPC9zdmc+Cg=="
    border-start:
      label: "Additive Start"
      icon: "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgZmlsbD0iY3VycmVudENvbG9yIiBjbGFzcz0iYmkgYmktYm9yZGVyLWxlZnQiIHZpZXdCb3g9IjAgMCAxNiAxNiI+CiAgPHBhdGggZD0iTTAgMHYxNmgxVjB6bTEuOTA2IDFoLjkzOFYwaC0uOTM4em0xLjg3NSAwaC45MzhWMEgzLjc4djF6bTEuODc1IDBoLjkzOFYwaC0uOTM4ek03LjUzMS45NjlWMWguOTM4Vi45NjlIOC41Vi41aC0uMDMxVjBINy41M3YuNUg3LjV2LjQ2OXpNOS40MDYgMWguOTM4VjBoLS45Mzh6bTEuODc1IDBoLjkzOFYwaC0uOTM4em0xLjg3NSAwaC45MzhWMGgtLjkzOHptMS44NzUgMGguNDY5Vi45NjloLjVWMGgtLjk2OXYuNUgxNXYuNDY5aC4wMzF6TTcuNSAxLjkwNnYuOTM4aDF2LS45Mzh6bTcuNSAwdi45MzhoMXYtLjkzOHpNNy41IDMuNzgxdi45MzhoMVYzLjc4aC0xem03LjUgMHYuOTM4aDFWMy43OGgtMXpNNy41IDUuNjU2di45MzhoMXYtLjkzOHptNy41IDB2LjkzOGgxdi0uOTM4ek0xLjkwNiA4LjVoLjkzOHYtMWgtLjkzOHptMS44NzUgMGguOTM4di0xSDMuNzh2MXptMS44NzUgMGguOTM4di0xaC0uOTM4em0yLjgxMyAwdi0uMDMxSDguNVY3LjUzaC0uMDMxVjcuNUg3LjUzdi4wMzFINy41di45MzhoLjAzMVY4LjV6bS45MzcgMGguOTM4di0xaC0uOTM4em0xLjg3NSAwaC45Mzh2LTFoLS45Mzh6bTEuODc1IDBoLjkzOHYtMWgtLjkzOHptMS44NzUgMGguNDY5di0uMDMxaC41VjcuNTNoLS41VjcuNWgtLjQ2OXYuMDMxSDE1di45MzhoLjAzMXpNNy41IDkuNDA2di45MzhoMXYtLjkzOHptOC41LjkzOHYtLjkzOGgtMXYuOTM4em0tOC41LjkzN3YuOTM4aDF2LS45Mzh6bTguNS45Mzh2LS45MzhoLTF2LjkzOHptLTguNS45Mzd2LjkzOGgxdi0uOTM4em04LjUuOTM4di0uOTM4aC0xdi45Mzh6TTEuOTA2IDE2aC45Mzh2LTFoLS45Mzh6bTEuODc1IDBoLjkzOHYtMUgzLjc4djF6bTEuODc1IDBoLjkzOHYtMWgtLjkzOHptMS44NzUtLjV2LjVoLjkzOHYtLjVIOC41di0uNDY5aC0uMDMxVjE1SDcuNTN2LjAzMUg3LjV2LjQ2OXptMS44NzUuNWguOTM4di0xaC0uOTM4em0xLjg3NSAwaC45Mzh2LTFoLS45Mzh6bTEuODc1IDBoLjkzOHYtMWgtLjkzOHptMS44NzUtLjV2LjVIMTZ2LS45NjloLS41VjE1aC0uNDY5di4wMzFIMTV2LjQ2OXoiLz4KPC9zdmc+Cg=="
    border-end:
      label: "Additive End"
      icon: "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgZmlsbD0iY3VycmVudENvbG9yIiBjbGFzcz0iYmkgYmktYm9yZGVyLXJpZ2h0IiB2aWV3Qm94PSIwIDAgMTYgMTYiPgogIDxwYXRoIGQ9Ik0uOTY5IDBIMHYuOTY5aC41VjFoLjQ2OVYuOTY5SDFWLjVILjk2OXptLjkzNyAxaC45MzhWMGgtLjkzOHptMS44NzUgMGguOTM4VjBIMy43OHYxem0xLjg3NSAwaC45MzhWMGgtLjkzOHpNNy41MzEuOTY5VjFoLjkzOFYuOTY5SDguNVYuNWgtLjAzMVYwSDcuNTN2LjVINy41di40Njl6TTkuNDA2IDFoLjkzOFYwaC0uOTM4em0xLjg3NSAwaC45MzhWMGgtLjkzOHptMS44NzUgMGguOTM4VjBoLS45Mzh6TTE2IDBoLTF2MTZoMXpNMSAyLjg0NHYtLjkzOEgwdi45Mzh6bTYuNS0uOTM4di45MzhoMXYtLjkzOHpNMSA0LjcxOVYzLjc4SDB2LjkzOGgxem02LjUtLjkzOHYuOTM4aDFWMy43OGgtMXpNMSA2LjU5NHYtLjkzOEgwdi45Mzh6bTYuNS0uOTM4di45MzhoMXYtLjkzOHpNLjUgOC41aC40Njl2LS4wMzFIMVY3LjUzSC45NjlWNy41SC41di4wMzFIMHYuOTM4aC41em0xLjQwNiAwaC45Mzh2LTFoLS45Mzh6bTEuODc1IDBoLjkzOHYtMUgzLjc4djF6bTEuODc1IDBoLjkzOHYtMWgtLjkzOHptMi44MTMgMHYtLjAzMUg4LjVWNy41M2gtLjAzMVY3LjVINy41M3YuMDMxSDcuNXYuOTM4aC4wMzFWOC41em0uOTM3IDBoLjkzOHYtMWgtLjkzOHptMS44NzUgMGguOTM4di0xaC0uOTM4em0xLjg3NSAwaC45Mzh2LTFoLS45Mzh6TTAgOS40MDZ2LjkzOGgxdi0uOTM4em03LjUgMHYuOTM4aDF2LS45Mzh6TTAgMTEuMjgxdi45MzhoMXYtLjkzOHptNy41IDB2LjkzOGgxdi0uOTM4ek0wIDEzLjE1NnYuOTM4aDF2LS45Mzh6bTcuNSAwdi45MzhoMXYtLjkzOHpNMCAxNmguOTY5di0uNUgxdi0uNDY5SC45NjlWMTVILjV2LjAzMUgwem0xLjkwNiAwaC45Mzh2LTFoLS45Mzh6bTEuODc1IDBoLjkzOHYtMUgzLjc4djF6bTEuODc1IDBoLjkzOHYtMWgtLjkzOHptMS44NzUtLjV2LjVoLjkzOHYtLjVIOC41di0uNDY5aC0uMDMxVjE1SDcuNTN2LjAzMUg3LjV2LjQ2OXptMS44NzUuNWguOTM4di0xaC0uOTM4em0xLjg3NSAwaC45Mzh2LTFoLS45Mzh6bTEuODc1IDBoLjkzOHYtMWgtLjkzOHoiLz4KPC9zdmc+Cg=="
  previewed_with:
    - p-2
    - bg-light
    - border-dark
  weight: -80
```

There are several options and all options are defined with a label AND an icon,
so the plugin source Toolbar will be used to render the style form element in
the UI.


### Example rendering a Select

Select source plugin is always applicable, and two types of rendering are
available.

Select with preview:

```yaml
colors_color:
  category: "Text"
  label: "Color"
  description: "Convey meaning through color with a handful of color utility classes."
  options:
    text-primary: "Primary"
    text-primary-emphasis: "Primary emphasis"
    text-secondary: "Secondary"
    text-secondary-emphasis: "Secondary emphasis"
    text-success: "Success"
    text-success-emphasis: "Success emphasis"
    text-danger: "Danger"
    text-danger-emphasis: "Danger emphasis"
    text-warning: "Warning"
    text-warning-emphasis: "Warning emphasis"
    text-info: "Info"
    text-info-emphasis: "Info emphasis"
    text-light: "Light"
    text-light-emphasis: "Light emphasis"
    text-dark: "Dark"
    text-dark-emphasis: "Dark emphasis"
    text-body: "Body"
    text-body-emphasis: "Body emphasis"
    text-body-secondary: "Body secondary"
    text-body-tertiary: "Body tertiary"
    text-white: "White"
    text-black: "Black"
    text-black-50: "Black 50 (deprecated)"
    text-white-50: "White 50 (deprecated)"
    text-muted: "Muted (deprecated)"
    text-reset: "Reset"
  weight: -98
```

There are several options defined solely by their labels. The select options
will be displayed with a preview on the left.

Classic select without preview:

```yaml
spacing_gap:
  category: "Spacing"
  label: "Gap"
  description: "When using display: grid or display: flex, you can make use of gap utilities on the parent element. This can save on having to add margin utilities to individual children of a grid or flex container. Gap utilities are responsive by default."
  options:
    gap-0: "0"
    gap-1: "1"
    gap-2: "2"
    gap-3: "3"
    gap-4: "4"
    gap-5: "5"
  previewed_as: hidden
  weight: -58
```

The property `previewed_as` with a value containing `hidden` (hidden,
widget_hidden for examples) disables the select with preview rendering.


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
