<?php

declare(strict_types = 1);

use Drupal\KernelTests\KernelTestBase;
use Drupal\ui_styles\Render\Element;

/**
 * Kernel tests for UI Styles Render element.
 *
 * @group ui_styles
 *
 * @coversDefaultClass \Drupal\ui_styles\Render\Element
 */
class UiStylesRenderElementTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'block'];

  /**
   * Test isAcceptingAttributes().
   *
   * @covers ::isAcceptingAttributes
   * @covers ::isRenderElementAcceptingAttributes
   * @covers ::isThemeHookAcceptingAttributes
   *
   * @dataProvider providerTestAttributes
   */
  public function testIsAcceptingAttributes(array $element, bool $expected): void {
    $result = Element::isAcceptingAttributes($element);
    $this->assertSame($expected, $result);
  }

  /**
   * Data provider for testIsAcceptingAttributes().
   */
  public function providerTestAttributes(): array {
    $data = [
      'already_exist' => [
        [
          '#attributes' => [
            'class' => [
              'original-class',
            ],
          ],
        ],
        TRUE,
      ],
      // isThemeHookAcceptingAttributes.
      'theme_has_attributes' => [
        [
          '#theme' => 'image',
          '#uri' => 'http://test.com/image.png',
        ],
        TRUE,
      ],
      'theme_no_attributes' => [
        [
          '#theme' => 'page_title',
          '#title' => 'My title',
        ],
        FALSE,
      ],
      'not_existing_theme' => [
        [
          '#theme' => 'not_existing_theme',
        ],
        FALSE,
      ],
      'theme_base_hook' => [
        [
          '#theme' => 'block__system_messages_block',
          'block' => 'something',
        ],
        TRUE,
      ],
      // @todo find a #theme with template to test.
      'invalid_theme' => [
        [
          '#not_valid_theme' => [
            'class' => [
              'original-class',
            ],
          ],
        ],
        FALSE,
      ],
      // isRenderElementAcceptingAttributes second part.
      'type_with_theme_valid' => [
        [
          '#type' => 'html_tag',
          '#theme' => 'image',
          '#uri' => 'http://test.com/image.png',
        ],
        TRUE,
      ],
      'type_with_theme_invalid' => [
        [
          '#type' => 'html_tag',
          '#theme' => 'not_existing_theme',
        ],
        FALSE,
      ],
      // isRenderElementAcceptingAttributes doCallback.
      // @todo find a #type with #pre_render to test.
    ];

    // isRenderElementAcceptingAttributes first part.
    foreach (Element::$typeWithoutAttributes as $attributes) {
      $data['type_without_attributes_' . $attributes] = [
        [
          '#type' => $attributes,
        ],
        FALSE,
      ];
    }
    foreach (Element::$typeWithAttributes as $attributes) {
      $data['type_with_attributes_' . $attributes] = [
        [
          '#type' => $attributes,
        ],
        TRUE,
      ];
    }

    return $data;
  }

}
