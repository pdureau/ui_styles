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
   * @covers ::getCategory
   * @covers ::getDescription
   * @covers ::getLabel
   * @covers ::getOptions
   * @covers ::getPreviewedAs
   * @covers ::getPreviewedWith
   * @covers ::getProvider
   * @covers ::getWeight
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
      ['getCategory', 'category', 'Plugin category'],
      ['getOptions', 'options', ['my-class' => 'My class']],
      ['getPreviewedWith', 'previewed_with', ['my-class']],
      ['getPreviewedAs', 'previewed_as', 'inside'],
      ['getPreviewedAs', 'previewed_as', 'aside'],
      ['getPreviewedAs', 'previewed_as', 'hidden'],
      ['getWeight', 'weight', 10],
      ['isEnabled', 'enabled', FALSE],
      ['isEnabled', 'enabled', TRUE],
    ];
  }

  /**
   * Test getOptionsAsOptions.
   *
   * @param array $options
   *   The options like in the YAML declaration.
   * @param array $expected
   *   The expected result.
   *
   * @covers ::getOptionsAsOptions
   *
   * @dataProvider definitionGetOptionsAsOptionsProvider
   */
  public function testGetOptionsAsOptions(array $options, array $expected): void {
    $definition = new StyleDefinition([
      'options' => $options,
    ]);
    $this->assertEquals($expected, $definition->getOptionsAsOptions());
  }

  /**
   * Provider.
   *
   * @return array
   *   Data.
   */
  public function definitionGetOptionsAsOptionsProvider(): array {
    return [
      [[
        'simple' => 'Simple',
        'complex' => [
          'label' => 'Complex',
        ],
      ], [
        'simple' => 'Simple',
        'complex' => 'Complex',
      ],
      ],
    ];
  }

  /**
   * Test getOptionsForPreview.
   *
   * @param array $style
   *   The style like in the YAML declaration.
   * @param array $expected
   *   The expected result.
   *
   * @covers ::getOptionsForPreview
   *
   * @dataProvider definitionGetOptionsForPreviewProvider
   */
  public function testGetOptionsForPreview(array $style, array $expected): void {
    $definition = new StyleDefinition($style);
    $this->assertEquals($expected, $definition->getOptionsForPreview());
  }

  /**
   * Provider.
   *
   * @return array
   *   Data.
   */
  public function definitionGetOptionsForPreviewProvider(): array {
    return [
      'simple' => [
        [
          'options' => [
            'simple' => 'Simple',
            'simple_bis' => 'Simple bis',
          ],
        ], [
          'simple' => [
            'label' => 'Simple',
            'description' => '',
            'previewed_with' => [],
            'previewed_as' => 'inside',
          ],
          'simple_bis' => [
            'label' => 'Simple bis',
            'description' => '',
            'previewed_with' => [],
            'previewed_as' => 'inside',
          ],
        ],
      ],
      'simple_with_previewed_with' => [
        [
          'options' => [
            'simple' => 'Simple',
            'simple_bis' => 'Simple bis',
          ],
          'previewed_with' => [
            'style-class',
          ],
        ], [
          'simple' => [
            'label' => 'Simple',
            'description' => '',
            'previewed_with' => [
              'style-class',
            ],
            'previewed_as' => 'inside',
          ],
          'simple_bis' => [
            'label' => 'Simple bis',
            'description' => '',
            'previewed_with' => [
              'style-class',
            ],
            'previewed_as' => 'inside',
          ],
        ],
      ],
      'simple_with_previewed_with_previewed_as' => [
        [
          'options' => [
            'simple' => 'Simple',
            'simple_bis' => 'Simple bis',
          ],
          'previewed_with' => [
            'style-class',
          ],
          'previewed_as' => 'hidden',
        ], [
          'simple' => [
            'label' => 'Simple',
            'description' => '',
            'previewed_with' => [
              'style-class',
            ],
            'previewed_as' => 'hidden',
          ],
          'simple_bis' => [
            'label' => 'Simple bis',
            'description' => '',
            'previewed_with' => [
              'style-class',
            ],
            'previewed_as' => 'hidden',
          ],
        ],
      ],
      'complex' => [
        [
          'options' => [
            'complex' => [
              'label' => 'Complex',
              'description' => 'Description',
              'previewed_with' => [
                'option-class',
              ],
            ],
          ],
        ], [
          'complex' => [
            'label' => 'Complex',
            'description' => 'Description',
            'previewed_with' => [
              'option-class',
            ],
            'previewed_as' => 'inside',
          ],
        ],
      ],
      'complex_with_previewed_with' => [
        [
          'options' => [
            'complex' => [
              'label' => 'Complex',
              'description' => 'Description',
              'previewed_with' => [
                'option-class',
              ],
            ],
          ],
          'previewed_with' => [
            'style-class',
          ],
        ], [
          'complex' => [
            'label' => 'Complex',
            'description' => 'Description',
            'previewed_with' => [
              'style-class',
              'option-class',
            ],
            'previewed_as' => 'inside',
          ],
        ],
      ],
      'complex_with_previewed_with_previewed_as' => [
        [
          'options' => [
            'complex' => [
              'label' => 'Complex',
              'description' => 'Description',
              'previewed_with' => [
                'option-class',
              ],
            ],
          ],
          'previewed_with' => [
            'style-class',
          ],
          'previewed_as' => 'hidden',
        ], [
          'complex' => [
            'label' => 'Complex',
            'description' => 'Description',
            'previewed_with' => [
              'style-class',
              'option-class',
            ],
            'previewed_as' => 'hidden',
          ],
        ],
      ],
    ];
  }

}
