<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles\Kernel\Source;

use Drupal\KernelTests\KernelTestBase;
use Drupal\ui_styles\Definition\StyleDefinition;
use Drupal\ui_styles\Source\SourcePluginManagerInterface;

/**
 * Base class for source plugins kernel tests.
 */
abstract class SourceTestBase extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'ui_styles',
  ];

  /**
   * The tested plugin ID.
   *
   * @var string
   */
  protected string $pluginId;

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
   * Tests isApplicable method.
   *
   * @covers ::isApplicable
   */
  public function testIsApplicable(): void {
    $testData = $this->isApplicableProvider();
    foreach ($testData as $data) {
      $styleDefinition = new StyleDefinition($data['styleDefinition']);
      /** @var \Drupal\ui_styles\Source\SourceInterface $source */
      $source = $this->sourcePluginManager->createInstance($this->pluginId);
      $this->assertSame($data['expected'], $source->isApplicable($styleDefinition));
    }
  }

  /**
   * Not a "real" PHPUnit provider.
   *
   * @return array
   *   Data.
   */
  public static function isApplicableProvider(): array {
    return [
      'default' => [
        'styleDefinition' => [],
        'expected' => TRUE,
      ],
    ];
  }

}
