// cSpell:ignore uistylesinlineediting uistylesinlineui

import UiStylesInlineEditing from './uistylesinlineediting';
import UiStylesInlineUI from './uistylesinlineui';
import {Plugin} from 'ckeditor5/src/core';

export default class UiStylesInline extends Plugin {
  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'UiStylesInline';
  }

  /**
   * @inheritDoc
   */
  static get requires() {
    return [UiStylesInlineEditing, UiStylesInlineUI];
  }
}
