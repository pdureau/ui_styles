<?php

declare(strict_types=1);

namespace Drupal\ui_styles\Plugin\UiStyles\Source;

use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ui_styles\Attribute\Source;
use Drupal\ui_styles\Definition\StyleDefinition;
use Drupal\ui_styles\Source\SourcePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Source Select widget.
 *
 * Used when there are multiple options. When on the front theme, use radios and
 * emulate a select with CSS to provide preview.
 */
#[Source(
  id: 'select',
  label: new TranslatableMarkup('Select'),
  weight: Select::WEIGHT,
)]
class Select extends SourcePluginBase {

  use StringTranslationTrait;

  public const WEIGHT = 10;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected RendererInterface $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetForm(StyleDefinition $definition, string $selected = ''): array {
    $previewedAs = $definition->getPreviewedAs();
    if (\str_contains($previewedAs, 'hidden')) {
      return [
        '#type' => 'select',
        '#title' => $definition->getLabel(),
        '#options' => $definition->getOptionsAsOptions(),
        '#empty_option' => $definition->getEmptyOption(),
        '#default_value' => $selected,
        '#weight' => $definition->getWeight(),
      ];
    }

    return [
      '#type' => 'radios',
      '#title' => $definition->getLabel(),
      '#options' => $this->renderOptions($definition),
      '#default_value' => $selected,
      '#weight' => $definition->getWeight(),
      '#attributes' => [
        'class' => [
          'ui-styles-source-select-plugin',
        ],
      ],
      '#attached' => [
        'library' => [
          'ui_styles/source_plugin_select',
        ],
      ],
    ];
  }

  /**
   * Return array of options for form element.
   *
   * @param \Drupal\ui_styles\Definition\StyleDefinition $definition
   *   The definition Style Plugin.
   *
   * @return array
   *   The array of options.
   */
  protected function renderOptions(StyleDefinition $definition): array {
    $options = [
      '' => $definition->getEmptyOption(),
    ];

    foreach ($definition->getOptionsWithIcon() as $option_id => $option) {
      $label = $option['label'];
      $icon = $option['icon'];
      if (!empty($icon)) {
        $iconRenderable = [
          '#prefix' => '<span class="ui-styles-source-select-plugin-option">',
          '#theme' => 'image',
          '#uri' => $icon,
          '#title' => $label,
          '#attributes' => ['loading' => 'lazy'],
          '#suffix' => '</span>' . $label,
        ];
        $options[$option_id] = $this->renderer->renderInIsolation($iconRenderable);
      }
      else {
        $classes = $option['previewed_with'] ?? [];
        $classes[] = 'ui-styles-source-select-plugin-option';
        $classes[] = $option_id;
        $options[$option_id] = '<span class="' . \implode(' ', $classes) . '">Αα</span>' . $label;
      }
    }

    return $options;
  }

}
