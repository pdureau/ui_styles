<?php

declare(strict_types = 1);

namespace Drupal\Tests\ui_styles\Kernel;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the UI Styles plugin manager.
 *
 * @group ui_styles
 */
class PluginTest extends KernelTestBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ui_styles',
    'ui_styles_test',
  ];

  /**
   * Tests that plugins can be provided by YAML files.
   */
  public function testDetectedPlugins(): void {
    /** @var \Drupal\ui_styles\StylePluginManagerInterface $styles_manager */
    $styles_manager = $this->container->get('plugin.manager.ui_styles');
    $definitions = $styles_manager->getDefinitions();

    $this->assertEquals(1, \count($definitions), 'There is one style detected.');

    $expectations = [
      'test' => [
        'id' => 'test',
        'provider' => 'ui_styles_test',
        'label' => $this->t('Test'),
        'description' => $this->t('Test plugin.'),
        'enabled' => TRUE,
      ],
    ];
    foreach ($expectations as $plugin_id => $expected_plugin_structure) {
      $definition_as_array = $definitions[$plugin_id]->toArray();
      foreach ($expected_plugin_structure as $key => $value) {
        $this->assertEquals($value, $definition_as_array[$key]);
      }
    }
  }

  /**
   * Test that it is possible to override an already declared plugin.
   */
  public function testOverridingDefinition(): void {
    $this->enableModules(['ui_styles_test_disabled']);

    // Test when the module overriding the definition is executed before.
    \module_set_weight('ui_styles_test_disabled', -1);
    /** @var \Drupal\ui_styles\StylePluginManagerInterface $styles_manager */
    $styles_manager = $this->container->get('plugin.manager.ui_styles');
    $this->assertArrayHasKey('test', $styles_manager->getDefinitions());

    // Test when the module overriding the definition is executed after.
    \module_set_weight('ui_styles_test_disabled', 1);
    \drupal_flush_all_caches();
    /** @var \Drupal\ui_styles\StylePluginManagerInterface $styles_manager */
    $styles_manager = $this->container->get('plugin.manager.ui_styles');
    $this->assertArrayNotHasKey('test', $styles_manager->getDefinitions());
  }

}
