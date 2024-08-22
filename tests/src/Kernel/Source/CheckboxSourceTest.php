<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles\Kernel\Source;

use Drupal\Component\Render\MarkupInterface;
use Drupal\ui_styles\Definition\StyleDefinition;

/**
 * @coversDefaultClass \Drupal\ui_styles\Plugin\UiStyles\Source\Checkbox
 *
 * @group ui_styles
 */
class CheckboxSourceTest extends SourceTestBase {

  /**
   * {@inheritdoc}
   */
  protected string $pluginId = 'checkbox';

  /**
   * {@inheritdoc}
   */
  public static function isApplicableProvider(): array {
    return [
      'one_option' => [
        'styleDefinition' => [
          'options' => [
            'simple' => 'Simple',
          ],
        ],
        'expected' => TRUE,
      ],
      'two_options' => [
        'styleDefinition' => [
          'options' => [
            'simple' => 'Simple',
            'second' => 'Second',
          ],
        ],
        'expected' => FALSE,
      ],
    ];
  }

  /**
   * Tests getWidgetForm method.
   *
   * @covers ::getWidgetForm
   */
  public function testGetWidgetForm(): void {
    /** @var \Drupal\ui_styles\Source\SourceInterface $source */
    $source = $this->sourcePluginManager->createInstance($this->pluginId);
    $styleDefinition = new StyleDefinition([
      'id' => 'test',
      'label' => 'Test label',
      'options' => [
        'simple' => 'Simple',
      ],
      'weight' => 5,
    ]);

    $expected = [
      '#type' => 'checkbox',
      '#title' => 'Test label: Simple',
      '#default_value' => '',
      '#return_value' => 'simple',
      '#weight' => 5,
    ];

    $form = $source->getWidgetForm($styleDefinition);
    $form['#title'] = $form['#title'] instanceof MarkupInterface ? $form['#title']->__toString() : $form['#title'];
    $this->assertEquals($expected, $form);

    // With a default value.
    $expected['#default_value'] = 'simple';
    $form = $source->getWidgetForm($styleDefinition, 'simple');
    $form['#title'] = $form['#title'] instanceof MarkupInterface ? $form['#title']->__toString() : $form['#title'];
    $this->assertEquals($expected, $form);
  }

}
