// cSpell:ignore uistylesblockediting uistylesblockui

import UiStylesBlockEditing from './uistylesblockediting';
import UiStylesBlockUI from './uistylesblockui';
import {Plugin} from 'ckeditor5/src/core';

export default class UiStylesBlock extends Plugin {
  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'UiStylesBlock';
  }

  /**
   * @inheritDoc
   */
  static get requires() {
    return [UiStylesBlockEditing, UiStylesBlockUI];
  }
}
