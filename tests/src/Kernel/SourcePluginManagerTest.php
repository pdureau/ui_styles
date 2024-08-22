<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles\Kernel;

use Drupal\Component\Plugin\PluginBase;
use Drupal\KernelTests\KernelTestBase;
use Drupal\ui_styles\Definition\StyleDefinition;
use Drupal\ui_styles\Source\SourcePluginManagerInterface;

/**
 * Test the source plugin manager.
 *
 * @group ui_styles
 *
 * @coversDefaultClass \Drupal\ui_styles\Source\SourcePluginManager
 */
class SourcePluginManagerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'ui_styles',
  ];

  /**
   * The source plugin manager.
   *
   * @var \Drupal\ui_styles\Source\SourcePluginManagerInterface
   */
  protected SourcePluginManagerInterface $sourcePluginManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->sourcePluginManager = $this->container->get('plugin.manager.ui_styles.source');
  }

  /**
   * @covers ::getApplicableSourcePlugin
   */
  public function testGetApplicableSourcePlugin(): void {
    $scenarios = [
      'one_option' => [
        'styleDefinition' => [
          'options' => [
            'one' => 'One',
          ],
        ],
        'expected' => 'checkbox',
      ],
      'multiple_options_no_icons' => [
        'styleDefinition' => [
          'options' => [
            'one' => 'One',
            'two' => 'Two',
          ],
        ],
        'expected' => 'select',
      ],
      'global_icon' => [
        'styleDefinition' => [
          'icon' => 'icon',
          'options' => [
            'one' => 'One',
            'two' => 'Two',
          ],
        ],
        'expected' => 'toolbar',
      ],
      'one_option_with_icon_and_global_icon' => [
        'styleDefinition' => [
          'icon' => 'icon',
          'options' => [
            'one' => 'One',
            'two' => [
              'label' => 'Two',
              'icon' => 'icon',
            ],
          ],
        ],
        'expected' => 'toolbar',
      ],
      'all_options_with_icon' => [
        'styleDefinition' => [
          'options' => [
            'one' => [
              'label' => 'One',
              'icon' => 'icon',
            ],
            'two' => [
              'label' => 'Two',
              'icon' => 'icon',
            ],
          ],
        ],
        'expected' => 'toolbar',
      ],
      'one_option_with_icon' => [
        'styleDefinition' => [
          'options' => [
            'one' => 'One',
            'two' => [
              'label' => 'Two',
              'icon' => 'icon',
            ],
          ],
        ],
        'expected' => 'select',
      ],
    ];

    foreach ($scenarios as $scenario) {
      $styleDefinition = new StyleDefinition($scenario['styleDefinition']);
      $source = $this->sourcePluginManager->getApplicableSourcePlugin($styleDefinition);
      $this->assertInstanceOf(PluginBase::class, $source);
      $this->assertEquals($scenario['expected'], $source->getPluginId());
    }
  }

}
