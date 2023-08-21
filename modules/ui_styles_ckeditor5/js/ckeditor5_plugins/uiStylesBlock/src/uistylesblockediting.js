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
    return ['GeneralHtmlSupport', 'DataSchema'];
  }

  init() {
    const editor = this.editor;
    const normalizedStyleDefinitions = normalizeConfig(editor.config.get('uiStylesBlock.options'));

    editor.commands.add('uiStylesBlock', new UiStylesBlockCommand(editor, normalizedStyleDefinitions));

    this._defineSchema();
  }

  /**
   * Allow the remove format plugin to remove the classes.
   */
  _defineSchema() {
    const schema = this.editor.model.schema;
    const htmlSupport = this.editor.plugins.get('GeneralHtmlSupport');
    const dataSchema = this.editor.plugins.get('DataSchema');

    // Loop on the blocks definitions to get the attribute name and add
    // formatting.
    for (const definition in schema.getDefinitions()) {
      const schemaDefinitions = dataSchema.getDefinitionsForModel(definition);
      const schemaDefinition = schemaDefinitions.find(schemaDefinition => (schemaDefinition.model == definition) && (schemaDefinition.isBlock == true));

      if (schemaDefinition === undefined) {
        continue;
      }

      const attributeName = htmlSupport.getGhsAttributeNameForElement(schemaDefinition.view);
      schema.setAttributeProperties(attributeName, {
        isFormatting: true
      });
    }

    // Even if htmlAttributes should no more exist. Set it in case of plugins
    // not updated.
    schema.setAttributeProperties('htmlAttributes', {
      isFormatting: true
    });
  }
}
