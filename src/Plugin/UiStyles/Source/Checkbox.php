<?php

declare(strict_types=1);

namespace Drupal\ui_styles\Plugin\UiStyles\Source;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ui_styles\Attribute\Source;
use Drupal\ui_styles\Definition\StyleDefinition;
use Drupal\ui_styles\Source\SourcePluginBase;

/**
 * Source Checkbox widget.
 *
 * Used when there is only one option.
 */
#[Source(
  id: 'checkbox',
  label: new TranslatableMarkup('Checkbox'),
  weight: 1,
)]
class Checkbox extends SourcePluginBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function isApplicable(StyleDefinition $definition): bool {
    $options = $definition->getOptions();
    return \count($options) == 1;
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetForm(StyleDefinition $definition, string $selected = ''): array {
    $options = $definition->getOptionsAsOptions();
    $value = \array_keys($options)[0];
    $title = $this->t('@style_label: @style_option', [
      '@style_label' => $definition->getLabel(),
      '@style_option' => \array_shift($options),
    ]);

    return [
      '#type' => 'checkbox',
      '#title' => $title,
      '#default_value' => $selected,
      '#return_value' => $value,
      '#weight' => $definition->getWeight(),
    ];
  }

}
