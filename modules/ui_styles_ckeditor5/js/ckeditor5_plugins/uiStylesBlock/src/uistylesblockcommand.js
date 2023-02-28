// cSpell:ignore schemadefinitions selectables

import {Command} from 'ckeditor5/src/core';
import {first} from 'ckeditor5/src/utils';
import defaultConfig from '@ckeditor/ckeditor5-html-support/src/schemadefinitions';

export default class UiStylesBlockCommand extends Command {

	constructor(editor, styleDefinitions) {
		super(editor);

    /**
     * Set of currently applied styles on the current selection.
     *
     * @readonly
     * @observable
     * @member {Array.<String>} #value
     */
    this.set('value', []);

    /**
     * Names of enabled styles (styles that can be applied to the current selection).
     *
     * @readonly
     * @observable
     * @member {Array.<String>} #enabledStyles
     */
    this.set('enabledStyles', []);

		this._styleDefinitions = styleDefinitions;
	}

  /**
   * @inheritDoc
   */
  refresh() {
    const model = this.editor.model;
    const selection = model.document.selection;

    const value = new Set();
    const enabledStyles = new Set();

    // Block styles.
    const firstBlock = first(selection.getSelectedBlocks());

    if (firstBlock) {
      const ancestorBlocks = firstBlock.getAncestors({includeSelf: true, parentFirst: true});

      for (const block of ancestorBlocks) {
        // E.g. reached a model table when the selection is in a cell.
        // The command should not modify ancestors of a table.
        if (model.schema.isLimit(block)) {
          break;
        }

        if (!model.schema.checkAttribute(block, 'htmlAttributes')) {
          continue;
        }

        for (const definition of this._styleDefinitions) {
          // Compared to CKE5 style plugin, here styles are always active.
          enabledStyles.add(definition.name);

          // Check if this block style is active.
          const ghsAttributeValue = block.getAttribute('htmlAttributes');

          if (hasAllClasses(ghsAttributeValue, definition.classes)) {
            value.add(definition.name);
          }
        }
      }
    }

    this.enabledStyles = Array.from(enabledStyles).sort();
    this.isEnabled = this.enabledStyles.length > 0;
    this.value = this.isEnabled ? Array.from(value).sort() : [];
  }

	execute({styleName}) {
		const model = this.editor.model;
		const selection = model.document.selection;
		const htmlSupport = this.editor.plugins.get('GeneralHtmlSupport');

		const definition = this._styleDefinitions.find(({name}) => name == styleName);

    const shouldAddStyle = !this.value.includes(definition.name);

    // As there is currently no method to get the current definitions from
    // dataSchema, loop on default definitions and add manually the only
    // Drupal core added element.
    defaultConfig.block.push({
      model: 'drupalMedia',
      view: 'drupal-media',
    });

		model.change(() => {
      let selectables;
      selectables = getAffectedBlocks(selection.getSelectedBlocks(), model.schema);
      for (const selectable of selectables) {
        // Get element from block name.
        const schemaDefinition = defaultConfig.block.find(schemaDefinition => schemaDefinition.model == selectable.name);

        if (schemaDefinition !== null) {
          if (shouldAddStyle) {
            htmlSupport.removeModelHtmlClass(schemaDefinition.view, definition.excluded_classes, selectable);
            htmlSupport.addModelHtmlClass(schemaDefinition.view, definition.classes, selectable);
          }
          else {
            htmlSupport.removeModelHtmlClass(schemaDefinition.view, definition.classes, selectable);
          }
        }
      }
		});
	}
}

// Verifies if all classes are present in the given GHS attribute.
function hasAllClasses(ghsAttributeValue, classes) {
  if (!ghsAttributeValue || !ghsAttributeValue.classes) {
    return false;
  }

  return classes.every(className => ghsAttributeValue.classes.includes(className));
}

// Returns a set of elements that should be affected by the block-style change.
function getAffectedBlocks(selectedBlocks, schema) {
	const blocks = new Set();

	for (const selectedBlock of selectedBlocks) {
		const ancestorBlocks = selectedBlock.getAncestors({includeSelf: true, parentFirst: true});

		for (const block of ancestorBlocks) {
			if (schema.isLimit(block)) {
				break;
			}

      blocks.add(block);
		}
	}

	return blocks;
}
