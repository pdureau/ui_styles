// cSpell:ignore uistylesinlinecommand

import {Plugin} from 'ckeditor5/src/core';
import UiStylesInlineCommand from './uistylesinlinecommand';
import {normalizeConfig} from './utils';

export default class UiStylesInlineEditing extends Plugin {
  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'UiStylesInlineEditing';
  }

  /**
   * @inheritDoc
   */
  static get requires() {
    return ['GeneralHtmlSupport'];
  }

  init() {
    const editor = this.editor;
    const normalizedStyleDefinitions = normalizeConfig(editor.config.get('uiStylesInline.options'));

    editor.commands.add('uiStylesInline', new UiStylesInlineCommand(editor, normalizedStyleDefinitions));

    this._defineSchema();
  }

  _defineSchema() {
    const schema = this.editor.model.schema;

    // Allow the remove format plugin to remove the classes.
    schema.setAttributeProperties('htmlAttributes', {
      isFormatting: true
    });

    // Allow the remove format plugin to remove the span.
    schema.setAttributeProperties('htmlSpan', {
      isFormatting: true
    });
  }
}
