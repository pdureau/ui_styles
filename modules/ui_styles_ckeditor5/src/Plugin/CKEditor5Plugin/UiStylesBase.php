<?php

declare(strict_types = 1);

namespace Drupal\ui_styles_ckeditor5\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition;
use Drupal\ckeditor5\Plugin\CKEditor5PluginElementsSubsetInterface;
use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\EditorInterface;
use Drupal\ui_styles\MachineNameTrait;
use Drupal\ui_styles\StylePluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * UI Styles base plugin class.
 */
abstract class UiStylesBase extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface, CKEditor5PluginElementsSubsetInterface, ContainerFactoryPluginInterface {
  use CKEditor5PluginConfigurableTrait;
  use MachineNameTrait;

  /**
   * The key to store multiple groups in form state.
   */
  public const MULTIPLE_GROUPS_KEY = 'ui_styles_multiple_groups';

  /**
   * The default configuration for this plugin.
   *
   * @var string[][]
   */
  public const DEFAULT_CONFIGURATION = [
    'enabled_styles' => [],
  ];

  /**
   * The styles plugin manager.
   *
   * @var \Drupal\ui_styles\StylePluginManagerInterface
   */
  protected StylePluginManagerInterface $stylesManager;

  /**
   * The CKE5 config key.
   *
   * @var string
   */
  protected string $ckeditor5ConfigKey;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param \Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\ui_styles\StylePluginManagerInterface $stylesManager
   *   The styles plugin manager.
   * @param \Drupal\Component\Transliteration\TransliterationInterface $transliteration
   *   The transliteration service.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    CKEditor5PluginDefinition $plugin_definition,
    StylePluginManagerInterface $stylesManager,
    TransliterationInterface $transliteration
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->stylesManager = $stylesManager;
    $this->transliteration = $transliteration;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    // @phpstan-ignore-next-line
    return new static(
      $configuration,
      $plugin_id,
      // @phpstan-ignore-next-line
      $plugin_definition,
      $container->get('plugin.manager.ui_styles'),
      $container->get('transliteration'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return self::DEFAULT_CONFIGURATION;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $grouped_plugin_definitions = $this->stylesManager->getGroupedDefinitions();
    if (empty($grouped_plugin_definitions)) {
      return $form;
    }
    $form_state->set(self::MULTIPLE_GROUPS_KEY, TRUE);
    if (\count($grouped_plugin_definitions) == 1) {
      $form_state->set(self::MULTIPLE_GROUPS_KEY, FALSE);
    }

    $form['enabled_styles'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Enabled Styles'),
      '#description' => $this->t('These are the styles types that will appear in the UI Styles dropdown.'),
      '#tree' => TRUE,
    ];

    // Until https://www.drupal.org/project/drupal/issues/2269823 is done, we
    // have to create the groups using separated form elements.
    foreach ($grouped_plugin_definitions as $groupedDefinitions) {
      $opened_group = FALSE;
      foreach ($groupedDefinitions as $definition) {
        $style_plugin_id = $definition->id();
        $default_value = \in_array($style_plugin_id, $this->configuration['enabled_styles'], TRUE) ? $style_plugin_id : NULL;

        // If the group has at least one style enabled. Display it opened.
        if (!$opened_group && $default_value !== NULL) {
          $opened_group = TRUE;
        }

        $plugin_element = [
          '#type' => 'checkbox',
          '#title' => !empty($definition->getLabel()) ? $definition->getLabel() : $style_plugin_id,
          '#return_value' => $style_plugin_id,
          '#default_value' => $default_value,
        ];

        // Create group if it does not exist yet.
        if ($form_state->get(self::MULTIPLE_GROUPS_KEY) && $definition->hasCategory()) {
          $group_key = $this->getMachineName($definition->getCategory());
          if (!isset($form['enabled_styles'][$group_key])) {
            $form['enabled_styles'][$group_key] = [
              '#type' => 'details',
              '#title' => $definition->getCategory(),
            ];
          }
          // @phpstan-ignore-next-line
          $form['enabled_styles'][$group_key]['#open'] = $opened_group;
          $form['enabled_styles'][$group_key][$style_plugin_id] = $plugin_element;
        }
        else {
          $form['enabled_styles'][$style_plugin_id] = $plugin_element;
        }
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $flattened_style_ids = [];
    /** @var array $form_value */
    $form_value = $form_state->getValue('enabled_styles');
    foreach ($form_value as $group_key => $group_styles) {
      // Style without group will directly be 0 or the style id.
      if (!\is_array($group_styles)) {
        $flattened_style_ids[$group_key] = $group_styles;
      }
      else {
        foreach ($group_styles as $style_id => $style_value) {
          $flattened_style_ids[$style_id] = $style_value;
        }
      }
    }

    $config_value = \array_values(\array_filter($flattened_style_ids));
    $form_state->setValue('enabled_styles', $config_value);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['enabled_styles'] = $form_state->getValue('enabled_styles');
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsSubset(): array {
    /** @var \Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition $plugin_definition */
    $plugin_definition = $this->getPluginDefinition();
    $subset = $plugin_definition->getElements();
    $subset = \array_diff($subset, ['<$any-html5-element class>']);
    $enabled_classes = $this->getEnabledStylesClasses();
    if (!empty($enabled_classes)) {
      $subset[] = '<$any-html5-element class="' . \implode(' ', $enabled_classes) . '">';
    }
    return $subset;
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $config = $static_plugin_config;
    $enabled_styles = $this->configuration['enabled_styles'];

    foreach ($enabled_styles as $plugin_id) {
      $definition = $this->stylesManager->getDefinition($plugin_id, FALSE);
      if ($definition == NULL) {
        continue;
      }

      $style_options = $definition->getOptionsAsOptions();
      $style_options_keys = \array_keys($style_options);
      $cke5_style_options = [];
      foreach ($style_options as $classes => $option_label) {
        $cke5_classes = \explode(' ', $classes);
        $cke5_style_options[] = [
          'name' => $option_label,
          'classes' => $cke5_classes,
          'excluded_classes' => $this->getExcludedClasses($style_options_keys, $cke5_classes),
        ];
      }

      $config[$this->ckeditor5ConfigKey]['options'][] = [
        'id' => $plugin_id,
        'label' => $definition->getLabel(),
        'options' => $cke5_style_options,
      ];
    }

    return $config;
  }

  /**
   * Extract CSS classes.
   *
   * @return array
   *   The list of CSS classes from all the enabled plugins.
   */
  protected function getEnabledStylesClasses(): array {
    $enabled_classes = [];
    $enabled_styles = $this->configuration['enabled_styles'];

    foreach ($enabled_styles as $plugin_id) {
      $definition = $this->stylesManager->getDefinition($plugin_id, FALSE);
      if ($definition == NULL) {
        continue;
      }

      foreach ($definition->getOptionsAsOptions() as $option_classes => $option_label) {
        $enabled_classes = \array_merge($enabled_classes, \explode(' ', $option_classes));
      }
    }
    return \array_unique($enabled_classes);
  }

  /**
   * Get the CSS classes to exclude when selecting an option.
   *
   * @param array $styleOptions
   *   The style options of the style plugin?
   * @param array $selectedOptionClasses
   *   The CSS classes of the style option?
   *
   * @return array
   *   The list of CSS classes to remove when selecting this style option.
   */
  protected function getExcludedClasses(array $styleOptions, array $selectedOptionClasses): array {
    $cke5_excluded_classes = [];
    foreach ($styleOptions as $styleOption) {
      $cke5_excluded_classes = \array_merge($cke5_excluded_classes, \explode(' ', $styleOption));
    }
    return \array_values(\array_diff($cke5_excluded_classes, $selectedOptionClasses));
  }

}
