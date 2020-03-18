<?php

namespace Drupal\ui_styles_layout_builder;

use Drupal\Core\Form\FormInterface;

/**
 * Defines an interface for ConfigureSectionForm.
 */
interface ConfigureSectionFormInterface extends FormInterface {

  /**
   * Get the layout section being modified.
   *
   * @return \Drupal\layout_builder\Section|null
   *   The layout section.
   */
  public function getCurrentSection();

  /**
   * Indicates whether the section is being added (false) or updated (true).
   *
   * @return bool
   *   The section status: added (false) or updated (true).
   */
  public function isUpdate();

}
