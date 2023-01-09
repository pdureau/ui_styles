<?php

declare(strict_types = 1);

namespace Drupal\Tests\ui_styles\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\ui_styles\Definition\StyleDefinition;

/**
 * @coversDefaultClass \Drupal\ui_styles\Definition\StyleDefinition
 *
 * @group ui_styles
 */
class StyleDefinitionTest extends UnitTestCase {

  /**
   * Test getters.
   *
   * @param string $getter
   *   The getter callback.
   * @param string $name
   *   The name of the plugin attributes.
   * @param mixed $value
   *   The attribute's value.
   *
   * @covers ::getDescription
   * @covers ::getLabel
   * @covers ::getOptions
   * @covers ::getPreviewedAs
   * @covers ::getPreviewedWith
   * @covers ::getProvider
   * @covers ::id
   * @covers ::isEnabled
   *
   * @dataProvider definitionGettersProvider
   */
  public function testGetters(string $getter, string $name, $value): void {
    $definition = new StyleDefinition([$name => $value]);
    // @phpstan-ignore-next-line
    $this->assertEquals(\call_user_func([$definition, $getter]), $value);
  }

  /**
   * Provider.
   *
   * @return array
   *   Data.
   */
  public function definitionGettersProvider(): array {
    return [
      ['getProvider', 'provider', 'my_module'],
      ['id', 'id', 'plugin_id'],
      ['getLabel', 'label', 'Plugin label'],
      ['getDescription', 'description', 'Plugin description.'],
      ['getOptions', 'options', ['my-class' => 'My class']],
      ['getPreviewedWith', 'previewed_with', ['my-class']],
      ['getPreviewedAs', 'previewed_as', 'inside'],
      ['getPreviewedAs', 'previewed_as', 'aside'],
      ['getPreviewedAs', 'previewed_as', 'hidden'],
      ['isEnabled', 'enabled', FALSE],
      ['isEnabled', 'enabled', TRUE],
    ];
  }

}
