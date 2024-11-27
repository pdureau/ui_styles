<?php

declare(strict_types=1);

namespace Drupal\ui_styles_ui_patterns\Plugin\UiPatterns\Source;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ui_patterns\Attribute\Source;
use Drupal\ui_patterns\SourcePluginBase;
use Drupal\ui_styles\StylePluginManagerInterface;
use Drupal\ui_styles\UiStylesUtility;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the source.
 */
#[Source(
  id: 'ui_styles_attributes',
  label: new TranslatableMarkup('Styles attributes'),
  description: new TranslatableMarkup('Handle CSS classes with UI Styles.'),
  prop_types: ['attributes']
)]
class AttributesStyles extends SourcePluginBase {

  /**
   * The styles plugin manager.
   *
   * @var \Drupal\ui_styles\StylePluginManagerInterface
   */
  protected StylePluginManagerInterface $stylesManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->stylesManager = $container->get('plugin.manager.ui_styles');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropValue(): mixed {
    $styles = $this->getSetting('styles');
    if (!\is_array($styles)) {
      return [];
    }

    $selected = UiStylesUtility::extractSelectedStyles($styles);
    $extra = $styles['_ui_styles_extra'] ?? '';

    $extra = \explode(' ', $extra);
    $classes = \array_merge($selected, $extra);
    $classes = \array_unique(\array_filter($classes));

    if (empty($classes)) {
      return [];
    }
    return [
      'class' => \array_values($classes),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $form = parent::settingsForm($form, $form_state);

    $styles = $this->getSetting('styles');
    if (!\is_array($styles)) {
      $styles = [];
    }
    $selected = UiStylesUtility::extractSelectedStyles($styles);
    $extra = $styles['_ui_styles_extra'] ?? '';

    $form['styles'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];
    $form['styles'] = $this->stylesManager->alterForm($form['styles'], $selected, $extra);
    return $form;
  }

}
