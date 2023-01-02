<?php

declare(strict_types = 1);

namespace Drupal\Tests\ui_styles\Kernel;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the UI Styles plugin manager sorting method.
 *
 * @coversDefaultClass \Drupal\ui_styles\StylePluginManager
 *
 * @group ui_styles
 */
class UiStylesManagerSortingTest extends KernelTestBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ui_styles',
    'ui_styles_test_sort_definitions',
  ];

  /**
   * Test StylesPluginManager::getSortedDefinitions.
   *
   * Source:
   * a: (Label 2)
   * b: Label 2
   * c: Label 1
   * d: Label 3
   * e: Label 1
   *
   * Expected sort:
   * c
   * e
   * a
   * b
   * d
   *
   * @covers ::getSortedDefinitions
   * @covers ::sortDefinitions
   */
  public function testDetectedPlugins(): void {
    /** @var \Drupal\ui_styles\StylePluginManagerInterface $styles_manager */
    $styles_manager = $this->container->get('plugin.manager.ui_styles');
    /** @var array $definitions */
    $definitions = $styles_manager->getSortedDefinitions();

    $keys = \array_keys($definitions);
    $expected_keys_order = [
      'c',
      'e',
      'a',
      'b',
      'd',
    ];
    $this->assertEquals($expected_keys_order, $keys);
  }

}
