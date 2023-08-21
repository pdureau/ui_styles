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
  }
}
