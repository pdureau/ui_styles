<?php

namespace Drupal\ui_styles;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\ui_styles\Render\Element;
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
  public function alterForm(array $form, $selected, $extra = '') {
    // Set form actions to a high weight, just so that we can make our form
    // style element appear right before them.
    $form['actions']['#weight'] = 100;
    $selected = $selected ?: [];
    foreach ($this->getDefinitions() as $definition) {
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
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function addClasses(array $element, $selected, $extra = '') {

    // Set styles classes.
    $selected = is_array($selected) ? array_values($selected) : [];
    $extra = explode(' ', $extra);
    $styles = array_merge($selected, $extra);
    $styles = array_filter($styles);

    if (count($styles) === 0) {
      return $element;
    }

    // Blocks are special.
    if (isset($element['#theme']) && $element['#theme'] === 'block') {
      // Try to add styles to block content insted of wrapper.
      $content = $this->addStyleToBlockContent($element['content'], $styles);
      if ($content) {
        $element['content'] = $content;
        return $element;
      }
    }

    if (Element::isAcceptingAttributes($element)) {
      $element = Element::addClasses($element, $styles);
    }
    return $element;
  }

  /**
   * Add style to block content instead of block wrapper.
   */
  private function addStyleToBlockContent(array $content, array $styles) {

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
      if (isset($content['_layout_builder']) && count(Element::children($content['_layout_builder'])) === 1) {
        $section = $content['_layout_builder'][0];
        if (Element::isAcceptingAttributes($section)) {
          $content['_layout_builder'][0] = Element::addClasses($section, $styles);
          return $content;
        }
      }
      return FALSE;
    }

    if (Element::isAcceptingAttributes($content)) {
      return Element::addClasses($content, $styles);
    }
    return NULL;

  }

  /**
   * Add style to field formatter items.
   */
  private function addStyleToFieldFormatterItems(array $content, array $styles, string $attr_property = '#attributes') {
    foreach (Element::children($content) as $delta) {
      if (!Element::isAcceptingAttributes($content[$delta])) {
        return NULL;
      }
      if (array_key_exists('#theme', $content[$delta]) && $content[$delta]['#theme'] === 'image_formatter') {
        $attr_property = '#item_attributes';
      }
      $content[$delta] = Element::addClasses($content[$delta], $styles, $attr_property);
    }
    return $content;
  }

}
