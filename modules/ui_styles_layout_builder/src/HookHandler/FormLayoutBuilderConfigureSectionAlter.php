<?php

declare(strict_types=1);

namespace Drupal\ui_styles_layout_builder\HookHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Layout Builder section form alter.
 */
class FormLayoutBuilderConfigureSectionAlter {

  use StringTranslationTrait;

  /**
   * Add UI Styles on section config form.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   */
  public function formAlter(array &$form, FormStateInterface $formState): void {
    /** @var \Drupal\layout_builder\Form\ConfigureSectionForm $formObject */
    $formObject = $formState->getFormObject();
    $section = $formObject->getCurrentSection();

    // Section.
    $form['ui_styles'] = [
      '#type' => 'container',
    ];
    $form['ui_styles']['section'] = [
      '#type' => 'ui_styles_styles',
      '#title' => $this->t('Section styles'),
      '#default_value' => [
        'selected' => $section->getThirdPartySetting('ui_styles', 'selected') ?: [],
        'extra' => $section->getThirdPartySetting('ui_styles', 'extra') ?: '',
      ],
    ];

    // Regions.
    /** @var array $regions_configuration */
    $regions_configuration = $section->getThirdPartySetting('ui_styles', 'regions', []);
    $regions = $section->getLayout()->getPluginDefinition()->getRegions();
    if (!empty($regions)) {
      $form['ui_styles']['regions'] = [
        '#type' => 'container',
      ];
    }
    foreach ($regions as $region_name => $region_infos) {
      $form['ui_styles']['regions'][$region_name] = [
        '#type' => 'ui_styles_styles',
        '#title' => $this->t('@region_label region styles', [
          '@region_label' => $region_infos['label'] ?? '',
        ]),
        '#default_value' => [
          'selected' => $regions_configuration[$region_name]['selected'] ?? [],
          'extra' => $regions_configuration[$region_name]['extra'] ?? '',
        ],
      ];
    }

    // Our submit handler must execute before the default one, because the
    // default handler stores the section & component data in the tempstore
    // and we need to update those objects before that happens.
    \array_unshift($form['#submit'], [$this, 'submitForm']);

    // Set form actions to a high weight, just so that we can make our form
    // style element appear right before them.
    $form['actions']['#weight'] = (int) 100;
  }

  /**
   * Custom submit handler for submitting Layout Builder section forms.
   *
   * This is used to persist the selected style to the layout configuration
   * array, which layout builder's ConfigureSectionForm will persist to section
   * storage.
   */
  public function submitForm(array $form, FormStateInterface $formState): void {
    /** @var \Drupal\layout_builder\Form\ConfigureSectionForm $formObject */
    $formObject = $formState->getFormObject();
    $section = $formObject->getCurrentSection();

    /** @var array $ui_styles */
    $ui_styles = $formState->getValue('ui_styles');

    // Section.
    $selected = $ui_styles['section']['selected'] ?? [];
    if (empty($selected)) {
      $section->unsetThirdPartySetting('ui_styles', 'selected');
    }
    else {
      $section->setThirdPartySetting('ui_styles', 'selected', $selected);
    }
    $extra = $ui_styles['section']['extra'] ?? '';
    if (empty($extra)) {
      $section->unsetThirdPartySetting('ui_styles', 'extra');
    }
    else {
      $section->setThirdPartySetting('ui_styles', 'extra', $extra);
    }

    // Regions.
    $regions = [];
    /** @var array $ui_styles_regions */
    $ui_styles_regions = $ui_styles['regions'] ?? [];
    foreach ($ui_styles_regions as $region_name => $region_styles) {
      $selected = $region_styles['selected'] ?? [];
      $extra = $region_styles['extra'] ?? '';
      if (!empty($selected)) {
        $regions[$region_name]['selected'] = $selected;
      }
      if (!empty($extra)) {
        $regions[$region_name]['extra'] = $extra;
      }
    }
    if (empty($regions)) {
      $section->unsetThirdPartySetting('ui_styles', 'regions');
    }
    else {
      $section->setThirdPartySetting('ui_styles', 'regions', $regions);
    }
  }

}
