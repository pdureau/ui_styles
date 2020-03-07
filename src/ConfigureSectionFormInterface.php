<?php

namespace Drupal\ui_styles;

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

}
