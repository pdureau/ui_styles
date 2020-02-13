<?php

namespace Drupal\layout_builder_classes;

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
    $this->setCacheBackend($cache_backend, 'style_plugin', ['style_plugin']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $this->discovery = new YamlDiscovery('style.plugin', $this->moduleHandler->getModuleDirectories() + $this->themeHandler->getThemeDirectories());
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
      $element_name = 'layout_builder_class_' . $id;
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
    $form['_layout_builder_classes_extra'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Extra classes'),
      '#description' => $this->t('You can add many values using spaces as separators'),
      '#default_value' => $extra ?: '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function addClasses(array &$target_element, $selected, $extra = '') {
    // TODO: Extend this with most of BlockComponentRenderArraySubscriber.
    // Prepare class attribute.
    $target_element['#attributes'] = isset($target_element['#attributes']) ? $target_element['#attributes'] : [];
    $target_element['#attributes']['class'] = isset($target_element['#attributes']['class']) ? $target_element['#attributes']['class'] : [];
    // Prepare selected.
    if (!$selected || !is_array($selected)) {
      $selected = [];
    }
    $selected = array_values($selected);
    // Add extra to selected.
    $extra = explode(' ', $extra);
    $selected = array_merge($selected, $extra);
    // Clean and set.
    if (isset($target_element['#theme']) && $target_element['#theme'] === 'block') {
      $selected = array_filter($selected);
      $target_element = $this->addStyleToBlockContent($target_element, $selected);
      // TODO: $build['#cache']['tags']?
    }
    else {
      // Add already existing classes to selected.
      $classes = $target_element['#attributes']['class'] ?: [];
      $selected = array_merge($selected, $classes);
      $selected = array_filter($selected);
      $target_element['#attributes']['class'] = $selected;
    }
  }

  /**
   * Add style to block content instead of block wrapper.
   */
  private function addStyleToBlockContent($build, $styles) {
    if (isset($build['content']['#type']) &&
      in_array($build['content']['#type'], ['pattern', 'html_tag', 'view'])) {
      if (!isset($build['content']['#attributes']['class'])) {
        $build['content']['#attributes'] = [
          'class' => [],
        ];
      }
      $classes = $build['content']['#attributes']['class'] ?: [];
      $build['content']['#attributes']['class'] = array_merge($classes, $styles);
      $build['#attributes']['class'] = array_diff($build['#attributes']['class'], $styles);
    }
    elseif (isset($build['content']['#theme']) &&
      $build['content']['#theme'] === 'field') {
      foreach (Element::children($build['content']) as $delta) {
        if ($build['content']['#formatter'] === 'media_thumbnail') {
          $build = $this->addStyleToFieldFormatterItem($build, $delta, $styles, '#item_attributes');
        }
        else {
          // For everything else but #type=processed_text.
          $build = $this->addStyleToFieldFormatterItem($build, $delta, $styles, '#attributes');
        }
      }
      $build['#attributes']['class'] = array_diff($build['#attributes']['class'], $styles);
    }
    return $build;
  }

  /**
   * Add style to field formatter item.
   */
  private function addStyleToFieldFormatterItem($build, $delta, $styles, $attr_property) {
    if (!isset($build['content'][$delta][$attr_property])) {
      $build['content'][$delta][$attr_property] = [];
    }
    if (is_array($build['content'][$delta][$attr_property])) {
      if (!isset($build['content'][$delta][$attr_property]['class'])) {
        $build['content'][$delta][$attr_property] = [
          'class' => [],
        ];
      }
      $classes = $build['content'][$delta][$attr_property]['class'] ?: [];
      $build['content'][$delta][$attr_property]['class'] = array_merge($classes, $styles);
    }
    elseif ($build['content'][$delta][$attr_property] instanceof Attribute) {
      $build['content'][$delta][$attr_property]->addClass($styles);
    }
    return $build;
  }

}
