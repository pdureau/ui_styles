<?php

declare(strict_types = 1);

namespace Drupal\ui_styles;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\ui_styles\Render\Element;

/**
 * Provides the default style_plugin manager.
 */
class StylePluginManager extends DefaultPluginManager implements StylePluginManagerInterface {
  use StringTranslationTrait;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Provides default values for all style_plugin plugins.
   *
   * @var array
   */
  protected $defaults = [
    // Add required and optional plugin properties.
    'id' => '',
    'enabled' => TRUE,
    'label' => '',
    'description' => '',
    'options' => [],
  ];

  /**
   * Constructs a new StylePluginManager object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   *   The string translation service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler, TranslationInterface $translation, CacheBackendInterface $cache_backend, MessengerInterface $messenger) {
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
    $this->stringTranslation = $translation;
    $this->setCacheBackend($cache_backend, 'ui_styles', ['ui_styles']);
    $this->alterInfo('ui_styles_styles');
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    $this->discovery = new YamlDiscovery('ui_styles', $this->moduleHandler->getModuleDirectories() + $this->themeHandler->getThemeDirectories());
    $this->discovery->addTranslatableProperty('label', 'label_context');
    $this->discovery->addTranslatableProperty('description', 'description_context');
    $this->discovery = new ContainerDerivativeDiscoveryDecorator($this->discovery);
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-ignore-next-line
   */
  protected function alterDefinitions(&$definitions) {
    foreach ($definitions as $definition_key => $definition_info) {
      if (isset($definition_info['enabled']) && !$definition_info['enabled']) {
        unset($definitions[$definition_key]);
        continue;
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
    parent::processDefinition($definition, $plugin_id);
    // @todo Add validation of the plugin definition here.
    if (empty($definition['id'])) {
      throw new PluginException(\sprintf('Example plugin property (%s) definition "id" is required.', $plugin_id));
    }
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
  public function alterForm(array $form, $selected = [], $extra = ''): array {
    // Set form actions to a high weight, just so that we can make our form
    // style element appear right before them.
    $form['actions']['#weight'] = (int) 100;
    $selected = $selected ?: [];
    foreach ($this->getDefinitions() as $definition) {
      /** @var array $definition */
      /** @var string $id */
      $id = $definition['id'];
      $element_name = 'ui_styles_' . $id;
      // @todo Test if possible to force $selected type to array.
      // @phpstan-ignore-next-line
      $default = $selected[$id] ?? '';
      $form[$element_name] = [
        '#type' => 'select',
        '#options' => $definition['options'],
        '#title' => $definition['label'],
        '#default_value' => $default,
        '#required' => FALSE,
        '#empty_option' => $this->t('- None -'),
        '#weight' => (int) 90,
      ];
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
  public function addClasses(array $element, $selected = [], $extra = ''): array {
    // Set styles classes.
    $extra = \explode(' ', $extra);
    // @todo Test if possible to force $selected type to array.
    // @phpstan-ignore-next-line
    $styles = \array_merge($selected, $extra);
    $styles = \array_unique(\array_filter($styles));

    if (\count($styles) === 0) {
      return $element;
    }

    // Blocks are special.
    if (isset($element['#theme']) && $element['#theme'] === 'block') {
      // Try to add styles to block content instead of wrapper.
      $content = $this->addStyleToBlockContent($element['content'], $styles);
      if ($content) {
        $element['content'] = $content;
        return $element;
      }
    }

    if (Element::isAcceptingAttributes($element)) {
      return Element::addClasses($element, $styles);
    }

    // This case should not happen, it means the render array is not a block or
    // is not accepting attributes.
    $type = $element['#type'] ?? 'render array';
    $type = $element['#theme'] ?? $type;
    $this->messenger->addWarning($this->t('UI Styles was not able to add attributes to @type.', ['@type' => $type]), TRUE);
    return $element;
  }

  /**
   * Add style to block content instead of block wrapper.
   */
  private function addStyleToBlockContent(array $content, array $styles): array {
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

    if (Element::isAcceptingAttributes($content)) {
      return Element::addClasses($content, $styles);
    }

    return $content;
  }

  /**
   * Add style to field formatter items.
   */
  private function addStyleToFieldFormatterItems(array $content, array $styles, string $attr_property = '#attributes'): array {
    foreach (Element::children($content) as $delta) {
      if (!Element::isAcceptingAttributes($content[$delta])) {
        return $content;
      }
      if (\array_key_exists('#theme', $content[$delta]) && $content[$delta]['#theme'] === 'image_formatter') {
        $attr_property = '#item_attributes';
      }
      $content[$delta] = Element::addClasses($content[$delta], $styles, $attr_property);
    }
    return $content;
  }

}
