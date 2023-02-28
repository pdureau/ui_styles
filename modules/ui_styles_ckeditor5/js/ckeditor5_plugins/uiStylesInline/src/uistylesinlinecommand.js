// cSpell:ignore schemadefinitions selectables

import {Command} from 'ckeditor5/src/core';

export default class UiStylesInlineCommand extends Command {

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
    const value = new Set();
    const enabledStyles = new Set();

    // Inline styles.
    for (const definition of this._styleDefinitions) {
      // Compared to CKE5 style plugin, here styles are always active.
      enabledStyles.add(definition.name);

      // Check if this inline style is active.
      const ghsAttributeValue = this._getValueFromFirstAllowedNode('htmlSpan');

      if (hasAllClasses(ghsAttributeValue, definition.classes)) {
        value.add(definition.name);
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

		model.change(() => {
      if (shouldAddStyle) {
        htmlSupport.removeModelHtmlClass('span', definition.excluded_classes, selection);
        htmlSupport.addModelHtmlClass('span', definition.classes, selection);
      }
      else {
        htmlSupport.removeModelHtmlClass('span', definition.classes, selection);
      }
		});
	}

  /**
   * Checks the attribute value of the first node in the selection that allows the attribute.
   * For the collapsed selection, returns the selection attribute.
   *
   * @private
   * @param {String} attributeName Name of the GHS attribute.
   * @returns {Object|null} The attribute value.
   */
  _getValueFromFirstAllowedNode(attributeName ) {
    const model = this.editor.model;
    const schema = model.schema;
    const selection = model.document.selection;

    if (selection.isCollapsed) {
      return selection.getAttribute(attributeName);
    }

    for (const range of selection.getRanges()) {
      for (const item of range.getItems()) {
        if (schema.checkAttribute(item, attributeName)) {
          return item.getAttribute(attributeName);
        }
      }
    }

    return null;
  }
}

// Verifies if all classes are present in the given GHS attribute.
function hasAllClasses(ghsAttributeValue, classes) {
  if (!ghsAttributeValue || !ghsAttributeValue.classes) {
    return false;
  }

  return classes.every(className => ghsAttributeValue.classes.includes(className));
}
