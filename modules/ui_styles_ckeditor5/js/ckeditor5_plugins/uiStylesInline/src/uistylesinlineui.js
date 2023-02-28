/**
 * @file registers the UI Styles toolbar button and binds functionality to it.
 */

import {Plugin} from 'ckeditor5/src/core';
import {Model, createDropdown, addListToDropdown, addToolbarToDropdown} from 'ckeditor5/src/ui';
import {Collection} from 'ckeditor5/src/utils';

export default class UiStylesInlineUI extends Plugin {
  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'UiStylesInlineUI';
  }

  /**
   * @inheritDoc
   */
  init() {
    const editor = this.editor;
    const componentFactory = editor.ui.componentFactory;
    const t = Drupal.t;
    const options = editor.config.get('uiStylesInline.options');

    // Prepare style buttons.
    options.forEach(style => {
      this._addButton(style);
    });

    componentFactory.add('UiStylesInline', locale => {
      const dropdownView = createDropdown(locale);
      const uiStylesInlineCommand = editor.commands.get('uiStylesInline');

      // The entire dropdown will be disabled together with the command (e.g.
      // when the editor goes read-only).
      dropdownView.bind('isEnabled').to(uiStylesInlineCommand);

      // Add existing style buttons to dropdown's toolbar.
      const buttons = [];
      options.forEach(style => {
        buttons.push(componentFactory.create(`UIStylesInline:${style.id}`));
      });
      addToolbarToDropdown(dropdownView, buttons, {enableActiveItemFocusOnDropdownOpen: false});

      // Configure dropdown properties and behavior.
      dropdownView.buttonView.set({
        label: t('Styles (inline)'),
        withText: true,
        tooltip: true,
      });

      dropdownView.toolbarView.isVertical = true;
      dropdownView.toolbarView.ariaLabel = t('UI Styles inline toolbar');

      // As it is (or seems to be) currently not possible to bind the isOn of
      // dropdownView.buttonView to the command, apply a class on dropdownView
      // and add custom styling.
      dropdownView.bind('class').to(uiStylesInlineCommand, 'value', value => {
        const classes = [
          'ck-ui-styles-inline-dropdown'
        ];
        if (value.length > 0) {
          classes.push('ck-ui-styles-inline-dropdown-active');
        }
        return classes.join(' ');
      });

      // Execute command.
      this.listenTo(dropdownView, 'execute', evt => {
        editor.execute(evt.source.commandName, {styleName: evt.source.commandParam});
        editor.editing.view.focus();
      });

      return dropdownView;
    });
  }

  /**
   * Helper method for initializing the button and linking it with an appropriate command.
   *
   * @private
   * @param {Array} style A style structure.
   */
  _addButton(style) {
    const editor = this.editor;

    editor.ui.componentFactory.add(`UIStylesInline:${style.id}`, locale => {
      const styleItemDefinitions = new Collection();
      const uiStylesInlineCommand = editor.commands.get('uiStylesInline');

      // Loop on style options.
      style.options.forEach(style_option => {
        const normalizedStyleOptionName = `${style.id}:${style_option.name}`;
        const styleDef = {
          type: 'button',
          model: new Model({
            commandName: 'uiStylesInline',
            commandParam: normalizedStyleOptionName,
            label: style_option.name,
            withText: true,
          })
        };

        // Mark style option active depending on the command.
        styleDef.model.bind('isOn').to(uiStylesInlineCommand, 'value', value => {
          return !!value.includes(normalizedStyleOptionName);
        });

        styleItemDefinitions.add(styleDef);
      });

      // UI Style inline plugin dropdown.
      const dropdownView = createDropdown(locale);
      // Add second level items.
      addListToDropdown(dropdownView, styleItemDefinitions);
      dropdownView.buttonView.set({
        label: style.label,
        withText: true,
      });

      // As it is (or seems to be) currently not possible to bind the isOn of
      // dropdownView.buttonView to the command, apply a class on dropdownView
      // and add custom styling.
      dropdownView.bind('class').to(uiStylesInlineCommand, 'value', value => {
        const classes = [
          'ck-ui-styles-inline-dropdown-style-dropdown'
        ];
        if (value.find(name => name.includes(`${style.id}`))) {
          classes.push('ck-ui-styles-inline-dropdown-style-dropdown-active');
        }
        return classes.join(' ');
      });

      return dropdownView;
    });
  }
}
