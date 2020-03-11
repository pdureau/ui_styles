<?php

namespace Drupal\ui_styles;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\StringTranslationTrait;

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
   * Provides default values for all style_plugin plugins.
   *
   * @var array
   */
  protected $defaults = [
    // Add required and optional plugin properties.
    'id' => '',
    'description' => '',
    'label' => '',
    'options' => [],
  ];

  /**
   * Constructs a new StylePluginManager object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   */
  public function __construct(ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler, CacheBackendInterface $cache_backend) {
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
    $this->setCacheBackend($cache_backend, 'ui_styles', ['ui_styles']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $this->discovery = new YamlDiscovery('ui_styles', $this->moduleHandler->getModuleDirectories() + $this->themeHandler->getThemeDirectories());
      $this->discovery->addTranslatableProperty('label', 'label_context');
      $this->discovery = new ContainerDerivativeDiscoveryDecorator($this->discovery);
    }
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);
    // TODO: Add validation of the plugin definition here.
    if (empty($definition['id'])) {
      throw new PluginException(sprintf('Example plugin property (%s) definition "is" is required.', $plugin_id));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function providerExists($provider) {
    return $this->moduleHandler->moduleExists($provider) || $this->themeHandler->themeExists($provider);
  }

  /**
   * {@inheritdoc}
   */
  public function alterForm(array &$form, array $styles, $selected, $extra = '') {
    // Set form actions to a high weight, just so that we can make our form
    // style element appear right before them.
    $form['actions']['#weight'] = 100;
    $selected = $selected ?: [];
    foreach ($styles as $definition) {
      $id = $definition['id'];
      $element_name = 'ui_styles_' . $id;
      $default = isset($selected[$id]) ? $selected[$id] : '';
      $form[$element_name] = [
        '#type' => 'select',
        '#options' => $definition['options'],
        '#title' => $definition['label'],
        '#default_value' => $default,
        '#required' => FALSE,
        '#empty_option' => $this->t('- None -'),
        '#weight' => 90,
      ];
    }
    $form['_ui_styles_extra'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Extra classes'),
      '#description' => $this->t('You can add many values using spaces as separators'),
      '#default_value' => $extra ?: '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function addClasses(array &$element, $selected, $extra = '') {
    $selected = is_array($selected) ? array_values($selected) : [];
    $extra = explode(' ', $extra);
    $styles = array_merge($selected, $extra);
    if (isset($element['#theme']) && $element['#theme'] === 'block') {
      // We are in a block.
      $inline = (isset($element['#base_plugin_id']) && $element['#base_plugin_id'] === 'inline_block');
      $element['content'] = $this->addStyleToBlockContent($element['content'], $styles, $inline);
      // TODO: $build['#cache']['tags']?
    }
    else {
      // We are in a layout.
      $element = $this->addStyleToWrapper($element, $styles);
    }
  }

  /**
   * Add style to block or layout wrapper.
   */
  private function addStyleToWrapper(array $wrapper, array $styles) {
    $wrapper['#attributes'] = isset($wrapper['#attributes']) ? $wrapper['#attributes'] : [];
    $classes = isset($wrapper['#attributes']['class']) ? $wrapper['#attributes']['class'] : [];
    $styles = array_merge($styles, $classes);
    $styles = array_filter($styles);
    $wrapper['#attributes']['class'] = $styles;
    return $wrapper;
  }

  /**
   * Add style to block content instead of block wrapper.
   */
  private function addStyleToBlockContent(array $content, array $styles, $inline = FALSE) {
    $styles = array_filter($styles);
    // Inline block.
    if ($inline) {
      $content['#attributes'] = isset($content['#attributes']) ? $content['#attributes'] : [];
      $classes = isset($content['#attributes']['class']) ? $content['#attributes']['class'] : [];
      $content['#attributes']['class'] = array_merge($classes, $styles);
    }
    // Render element.
    elseif (isset($content['#type']) &&
      in_array($content['#type'], ['pattern', 'html_tag', 'view'])) {
      $content['#attributes'] = isset($content['#attributes']) ? $content['#attributes'] : [];
      $classes = isset($content['#attributes']['class']) ? $content['#attributes']['class'] : [];
      $content['#attributes']['class'] = array_merge($classes, $styles);
    }
    // Field formatter.
    elseif (isset($content['#theme']) && $content['#theme'] === 'field') {
      if ($content['#formatter'] === 'media_thumbnail') {
        $content = $this->addStyleToFieldFormatterItems($content, $styles, '#item_attributes');
      }
      else {
        $content = $this->addStyleToFieldFormatterItems($content, $styles, '#attributes');
      }
    }
    return $content;
  }

  /**
   * Add style to field formatter items.
   */
  private function addStyleToFieldFormatterItems(array $content, array $styles, string $attr_property) {
    foreach (Element::children($content) as $delta) {
      if (!isset($content[$delta][$attr_property])) {
        $content[$delta][$attr_property] = [];
      }
      // TODO: AttributeHelper https://www.drupal.org/node/3110716 in D8.9.
      if (is_array($content[$delta][$attr_property])) {
        if (!isset($content[$delta][$attr_property]['class'])) {
          $content[$delta][$attr_property] = [
            'class' => [],
          ];
        }
        $classes = $content[$delta][$attr_property]['class'] ?: [];
        $content[$delta][$attr_property]['class'] = array_merge($classes, $styles);
      }
      elseif ($content[$delta][$attr_property] instanceof Attribute) {
        $content[$delta][$attr_property]->addClass($styles);
      }
    }
    return $content;
  }

}
