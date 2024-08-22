<?php

declare(strict_types=1);

namespace Drupal\ui_styles_test\Plugin\UiStyles\Source;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ui_styles\Attribute\Source;
use Drupal\ui_styles\Definition\StyleDefinition;
use Drupal\ui_styles\Source\SourceInterface;

/**
 * Source Select widget used for tests, legacy behavior.
 */
#[Source(
  id: 'test_select',
  label: new TranslatableMarkup('Test select'),
)]
class TestSelect extends PluginBase implements SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function isApplicable(StyleDefinition $definition): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetForm(StyleDefinition $definition, string $selected = ''): array {
    return [
      '#type' => 'select',
      '#title' => $definition->getLabel(),
      '#options' => $definition->getOptionsAsOptions(),
      '#empty_option' => '- None -',
      '#default_value' => $selected,
      '#weight' => $definition->getWeight(),
    ];
  }

}
