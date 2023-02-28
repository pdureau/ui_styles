<?php

declare(strict_types = 1);

namespace Drupal\ui_styles;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ui_styles\Definition\StyleDefinition;
use Drupal\ui_styles\Render\Element;

/**
 * Provides the default style plugin manager.
 *
 * @method \Drupal\ui_styles\Definition\StyleDefinition|null getDefinition($plugin_id, $exception_on_invalid = TRUE)
 * @method \Drupal\ui_styles\Definition\StyleDefinition[] getDefinitions()
 */
class StylePluginManager extends DefaultPluginManager implements StylePluginManagerInterface {
  use StringTranslationTrait;
  use MachineNameTrait;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected ThemeHandlerInterface $themeHandler;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Component\Transliteration\TransliterationInterface $transliteration
   *   The transliteration service.
   */
  public function __construct(
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler,
    ThemeHandlerInterface $theme_handler,
    TransliterationInterface $transliteration
  ) {
    $this->setCacheBackend($cache_backend, 'ui_styles', ['ui_styles']);
    $this->alterInfo('ui_styles_styles');
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
    $this->transliteration = $transliteration;

    // Set defaults in the constructor to be able to use string translation.
    $this->defaults = [
      'id' => '',
      'enabled' => TRUE,
      'label' => '',
      'description' => '',
      'category' => $this->t('Other'),
      'options' => [],
      'previewed_with' => [],
      'previewed_as' => 'inside',
      'weight' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    $this->discovery = new YamlDiscovery('ui_styles', $this->moduleHandler->getModuleDirectories() + $this->themeHandler->getThemeDirectories());
    $this->discovery->addTranslatableProperty('label', 'label_context');
    $this->discovery->addTranslatableProperty('description', 'description_context');
    $this->discovery->addTranslatableProperty('category', 'category_context');
    $this->discovery = new ContainerDerivativeDiscoveryDecorator($this->discovery);
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   */
  public function getCategories() {
    // Fetch all categories from definitions and remove duplicates.
    $categories = \array_unique(\array_values(\array_map(static function (StyleDefinition $definition) {
      return $definition->getCategory();
    }, $this->getDefinitions())));
    \natcasesort($categories);
    // @phpstan-ignore-next-line
    return $categories;
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-ignore-next-line
   */
  public function getSortedDefinitions(?array $definitions = NULL): array {
    $definitions = $definitions ?? $this->getDefinitions();

    \uasort($definitions, static function (StyleDefinition $item1, StyleDefinition $item2) {
      // Sort by category.
      $category1 = $item1->getCategory();
      if ($category1 instanceof TranslatableMarkup) {
        $category1 = $category1->render();
      }
      $category2 = $item2->getCategory();
      if ($category2 instanceof TranslatableMarkup) {
        $category2 = $category2->render();
      }
      if ($category1 != $category2) {
        return \strnatcasecmp($category1, $category2);
      }

      // Sort by weight.
      $weight = $item1->getWeight() <=> $item2->getWeight();
      if ($weight != 0) {
        return $weight;
      }

      // Sort by label ignoring parenthesis.
      $label1 = $item1->getLabel();
      if ($label1 instanceof TranslatableMarkup) {
        $label1 = $label1->render();
      }
      $label2 = $item2->getLabel();
      if ($label2 instanceof TranslatableMarkup) {
        $label2 = $label2->render();
      }
      // Ignore parenthesis.
      $label1 = \str_replace(['(', ')'], '', $label1);
      $label2 = \str_replace(['(', ')'], '', $label2);
      if ($label1 != $label2) {
        return \strnatcasecmp($label1, $label2);
      }

      // Sort by plugin ID.
      // In case the plugin ID starts with an underscore.
      $id1 = \str_replace('_', '', $item1->id());
      $id2 = \str_replace('_', '', $item2->id());
      return \strnatcasecmp($id1, $id2);
    });

    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupedDefinitions(?array $definitions = NULL): array {
    $definitions = $this->getSortedDefinitions($definitions ?? $this->getDefinitions());
    $grouped_definitions = [];
    foreach ($definitions as $id => $definition) {
      $grouped_definitions[(string) $definition->getCategory()][$id] = $definition;
    }
    return $grouped_definitions;
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-ignore-next-line
   */
  protected function alterDefinitions(&$definitions) {
    /** @var \Drupal\ui_styles\Definition\StyleDefinition[] $definitions */
    foreach ($definitions as $definition_key => $definition) {
      if (!$definition->isEnabled()) {
        unset($definitions[$definition_key]);
      }
    }

    parent::alterDefinitions($definitions);
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-ignore-next-line
   */
  public function processDefinition(&$definition, $plugin_id): void {
    // Call parent first to set defaults while still manipulating an array.
    // Otherwise, as there is currently no derivative system among CSS variable
    // plugins, there is no deriver or class attributes.
    parent::processDefinition($definition, $plugin_id);

    if (empty($definition['id'])) {
      throw new PluginException(\sprintf('Style plugin property (%s) definition "id" is required.', $plugin_id));
    }

    $definition = new StyleDefinition($definition);
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-ignore-next-line
   */
  protected function providerExists($provider): bool {
    return $this->moduleHandler->moduleExists($provider) || $this->themeHandler->themeExists($provider);
  }

  /**
   * {@inheritdoc}
   */
  public function alterForm(array $form, array $selected = [], string $extra = ''): array {
    $grouped_plugin_definitions = $this->getGroupedDefinitions();
    if (empty($grouped_plugin_definitions)) {
      return $form;
    }
    $multiple_groups = TRUE;
    if (\count($grouped_plugin_definitions) == 1) {
      $multiple_groups = FALSE;
    }

    foreach ($grouped_plugin_definitions as $group_plugin_definitions) {
      foreach ($group_plugin_definitions as $definition) {
        $id = $definition->id();
        $element_name = 'ui_styles_' . $id;
        $plugin_element = [
          '#type' => 'select',
          '#title' => $definition->getLabel(),
          '#options' => $definition->getOptionsAsOptions(),
          '#empty_option' => $this->t('- None -'),
          '#default_value' => $selected[$id] ?? '',
          '#weight' => $definition->getWeight(),
        ];

        // Create group if it does not exist yet.
        if ($multiple_groups && $definition->hasCategory()) {
          $group_key = $this->getMachineName($definition->getCategory());
          if (!isset($form[$group_key])) {
            $form[$group_key] = [
              '#type' => 'details',
              '#title' => $definition->getCategory(),
              '#open' => FALSE,
            ];
          }

          $form[$group_key][$element_name] = $plugin_element;
        }
        else {
          $form[$element_name] = $plugin_element;
        }
      }
    }
    $form['_ui_styles_extra'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Extra classes'),
      '#description' => $this->t('You can add many values using spaces as separators'),
      '#default_value' => $extra ?: '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function addClasses(array $element, array $selected = [], string $extra = ''): array {
    // Set styles classes.
    $extra = \explode(' ', $extra);
    $styles = \array_merge($selected, $extra);
    $styles = \array_unique(\array_filter($styles));

    if (\count($styles) === 0) {
      return $element;
    }

    // Blocks are special.
    if (isset($element['#theme'])
      && $element['#theme'] === 'block'
      && isset($element['content'])
      && !empty($element['content'])
    ) {
      // Try to add styles to block content instead of wrapper.
      $element['content'] = $this->addStyleToBlockContent($element['content'], $styles);
      return $element;
    }

    Element::wrapElementIfNotAcceptingAttributes($element);
    return Element::addClasses($element, $styles);
  }

  /**
   * Add styles to block content instead of block wrapper.
   */
  protected function addStyleToBlockContent(array $content, array $styles): array {
    // Field formatters are special.
    if (isset($content['#theme']) && $content['#theme'] === 'field') {
      if ($content['#formatter'] === 'media_thumbnail') {
        return $this->addStyleToFieldFormatterItems($content, $styles, '#item_attributes');
      }
      return $this->addStyleToFieldFormatterItems($content, $styles);
    }

    // Embedded entity displays are special.
    if (isset($content['#view_mode'])) {
      // Let's deal only with single section layout builder for now.
      if (isset($content['_layout_builder']) && \count(Element::children($content['_layout_builder'])) === 1) {
        $section = $content['_layout_builder'][0];
        if (Element::isAcceptingAttributes($section)) {
          $content['_layout_builder'][0] = Element::addClasses($section, $styles);
        }
      }
      return $content;
    }

    Element::wrapElementIfNotAcceptingAttributes($content);

    return Element::addClasses($content, $styles);
  }

  /**
   * Add style to field formatter items.
   */
  protected function addStyleToFieldFormatterItems(array $content, array $styles, string $attr_property = '#attributes'): array {
    foreach (Element::children($content) as $delta) {
      Element::wrapElementIfNotAcceptingAttributes($content[$delta]);

      if (\array_key_exists('#theme', $content[$delta]) && $content[$delta]['#theme'] === 'image_formatter') {
        $attr_property = '#item_attributes';
      }
      $content[$delta] = Element::addClasses($content[$delta], $styles, $attr_property);
    }
    return $content;
  }

}
