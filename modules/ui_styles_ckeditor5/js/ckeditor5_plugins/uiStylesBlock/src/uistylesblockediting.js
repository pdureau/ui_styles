// cSpell:ignore uistylesblockcommand

import {Plugin} from 'ckeditor5/src/core';
import UiStylesBlockCommand from './uistylesblockcommand';
import {normalizeConfig} from './utils';

export default class UiStylesBlockEditing extends Plugin {
  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'UiStylesBlockEditing';
  }

  /**
   * @inheritDoc
   */
  static get requires() {
    return ['GeneralHtmlSupport'];
  }

  init() {
    const editor = this.editor;
    const normalizedStyleDefinitions = normalizeConfig(editor.config.get('uiStylesBlock.options'));

    editor.commands.add('uiStylesBlock', new UiStylesBlockCommand(editor, normalizedStyleDefinitions));

    this._defineSchema();
  }

  _defineSchema() {
    const schema = this.editor.model.schema;

    // Allow the remove format plugin to remove the classes.
    schema.setAttributeProperties('htmlAttributes', {
      isFormatting: true
    });
  }
}
