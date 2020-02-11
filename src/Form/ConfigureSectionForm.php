<?php

namespace Drupal\layout_builder_classes\Form;

use Drupal\layout_builder\Form\ConfigureSectionForm as OriginalConfigureSectionForm;

/**
 * Class ConfigureSectionForm.
 *
 * Extend the original form to expose the layout object.
 * See https://www.drupal.org/i/3044117
 */
class ConfigureSectionForm extends OriginalConfigureSectionForm {

  /**
   * Get the layout plugin being modified.
   *
   * @return \Drupal\Core\Layout\LayoutInterface|\Drupal\Core\Plugin\PluginFormInterface
   *   The layout plugin object.
   */
  public function getLayout() {
    return $this->layout;
  }

  /**
   * Get the layout section being modified.
   *
   * @return \Drupal\layout_builder\Section
   *   The layout section.
   */
  public function getCurrentSection() {
    return $this->sectionStorage->getSection($this->delta);
  }

}
