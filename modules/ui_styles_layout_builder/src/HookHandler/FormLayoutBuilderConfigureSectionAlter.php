<?php

declare(strict_types=1);

namespace Drupal\ui_styles_layout_builder\HookHandler;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ui_styles\StylePluginManagerInterface;
use Drupal\ui_styles\UiStylesUtility;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Layout Builder section form alter.
 */
class FormLayoutBuilderConfigureSectionAlter implements ContainerInjectionInterface {

  use DependencySerializationTrait;
  use StringTranslationTrait;

  /**
   * The styles plugin manager.
   *
   * @var \Drupal\ui_styles\StylePluginManagerInterface
   */
  protected StylePluginManagerInterface $stylesManager;

  /**
   * Constructor.
   *
   * @param \Drupal\ui_styles\StylePluginManagerInterface $stylesManager
   *   The styles plugin manager.
   */
  public function __construct(StylePluginManagerInterface $stylesManager) {
    $this->stylesManager = $stylesManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    // @phpstan-ignore-next-line
    return new static(
      $container->get('plugin.manager.ui_styles')
    );
  }

  /**
   * Add UI Styles on section config form.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   */
  public function formAlter(array &$form, FormStateInterface $formState): void {
    if (empty($this->stylesManager->getGroupedDefinitions())) {
      return;
    }

    /** @var \Drupal\layout_builder\Form\ConfigureSectionForm $formObject */
    $formObject = $formState->getFormObject();
    $section = $formObject->getCurrentSection();

    // Section.
    /** @var array $selected */
    $selected = $section->getThirdPartySetting('ui_styles', 'selected') ?: [];
    /** @var string $extra */
    $extra = $section->getThirdPartySetting('ui_styles', 'extra') ?: '';
    $form['ui_styles'] = [
      '#type' => 'container',
    ];
    $form['ui_styles']['section'] = [
      '#type' => 'details',
      '#title' => $this->t('Section styles'),
      '#open' => FALSE,
    ];
    $form['ui_styles']['section'] = $this->stylesManager->alterForm($form['ui_styles']['section'], $selected, $extra);

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
      /** @var array $selected */
      $selected = $regions_configuration[$region_name]['selected'] ?? [];
      /** @var string $extra */
      $extra = $regions_configuration[$region_name]['extra'] ?? '';
      $form['ui_styles']['regions'][$region_name] = [
        '#type' => 'details',
        '#title' => $this->t('@region_label region styles', [
          '@region_label' => $region_infos['label'] ?? '',
        ]),
        '#open' => FALSE,
      ];
      $form['ui_styles']['regions'][$region_name] = $this->stylesManager->alterForm($form['ui_styles']['regions'][$region_name], $selected, $extra);
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
    $section->setThirdPartySetting('ui_styles', 'selected', UiStylesUtility::extractSelectedStyles($ui_styles['section']));
    $section->setThirdPartySetting('ui_styles', 'extra', $ui_styles['section']['_ui_styles_extra']);

    // Regions.
    $regions = [];
    /** @var array $ui_styles_regions */
    $ui_styles_regions = $ui_styles['regions'] ?? [];
    foreach ($ui_styles_regions as $region_name => $region_styles) {
      $regions[$region_name] = [
        'selected' => UiStylesUtility::extractSelectedStyles($region_styles),
        'extra' => $region_styles['_ui_styles_extra'],
      ];
    }
    $section->setThirdPartySetting('ui_styles', 'regions', $regions);
  }

}
