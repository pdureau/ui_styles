<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles\Kernel\Source;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Render\Markup;
use Drupal\ui_styles\Definition\StyleDefinition;

/**
 * @coversDefaultClass \Drupal\ui_styles\Plugin\UiStyles\Source\Toolbar
 *
 * @group ui_styles
 */
class ToolbarSourceTest extends SourceTestBase {

  /**
   * {@inheritdoc}
   */
  protected string $pluginId = 'toolbar';

  /**
   * The extension path resolver.
   *
   * @var \Drupal\Core\Extension\ExtensionPathResolver
   */
  protected ExtensionPathResolver $extensionPathResolver;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->extensionPathResolver = $this->container->get('extension.path.resolver');
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicableProvider(): array {
    return [
      'no_icons' => [
        'styleDefinition' => [
          'options' => [
            'simple' => 'Simple',
          ],
        ],
        'expected' => FALSE,
      ],
      'global_icon' => [
        'styleDefinition' => [
          'icon' => 'icon',
          'options' => [
            'simple' => 'Simple',
          ],
        ],
        'expected' => TRUE,
      ],
      'icon_on_all_options' => [
        'styleDefinition' => [
          'options' => [
            'option_1' => [
              'label' => 'Option 1',
              'icon' => 'icon',
            ],
            'option_2' => [
              'label' => 'Option 2',
              'icon' => 'icon',
            ],
          ],
        ],
        'expected' => TRUE,
      ],
      'missing_one_icon' => [
        'styleDefinition' => [
          'options' => [
            'option_1' => [
              'label' => 'Option 1',
              'icon' => 'icon',
            ],
            'option_2' => [
              'label' => 'Option 2',
            ],
          ],
        ],
        'expected' => FALSE,
      ],
      'missing_one_icon_and_global' => [
        'styleDefinition' => [
          'icon' => 'global',
          'options' => [
            'option_1' => [
              'label' => 'Option 1',
              'icon' => 'icon',
            ],
            'option_2' => [
              'label' => 'Option 2',
            ],
          ],
        ],
        'expected' => TRUE,
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
      'icon' => 'icon',
      'options' => [
        'simple' => 'Simple',
      ],
      'weight' => 5,
    ]);
    $empty_option_src = $this->extensionPathResolver->getPath('module', 'ui_styles') . '/assets/images/na-icon.png';

    $expected = [
      '#type' => 'radios',
      '#title' => 'Test label',
      '#options' => [
        '' => Markup::create("<img loading=\"lazy\" src=\"/{$empty_option_src}\" alt=\"\" title=\"- None -\" />\n"),
        'simple' => Markup::create("<img loading=\"lazy\" src=\"/icon\" alt=\"\" title=\"Simple\" />\n"),
      ],
      '#default_value' => '',
      '#weight' => 5,
      '#attributes' => [
        'class' => [
          'ui-styles-source-toolbar-plugin',
        ],
      ],
      '#attached' => [
        'library' => [
          'ui_styles/source_plugin_toolbar',
        ],
      ],
    ];
    // Convert to avoid objects comparison.
    foreach ($expected['#options'] as &$option) {
      $option = $option instanceof MarkupInterface ? $option->__toString() : $option;
    }

    $form = $source->getWidgetForm($styleDefinition);
    foreach ($form['#options'] as &$option) {
      $option = $option instanceof MarkupInterface ? $option->__toString() : $option;
    }
    $this->assertEquals($expected, $form);

    // With a default value.
    $expected['#default_value'] = 'simple';
    $form = $source->getWidgetForm($styleDefinition, 'simple');
    foreach ($form['#options'] as &$option) {
      $option = $option instanceof MarkupInterface ? $option->__toString() : $option;
    }
    $this->assertEquals($expected, $form);
  }

}
