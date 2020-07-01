<?php

/**
 * @file
 * Namespace Drupal\Tests\ui_styles\Kernel;.
 */

Use Drupal\KernelTests\KernelTestBase;
use Drupal\ui_styles\Render\Element;

/**
 * Class UiStylesRenderElementTest.
 *
 * @group ui_styles
 *
 * @coversDefaultClass \Drupal\ui_styles\Render\Element
 */
Class UiStylesRenderElementTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['system', 'block'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
  }

  /**
   * Test the isAcceptingAttributes().
   *
   * @covers ::isAcceptingAttributes
   * @covers ::isThemeHookAcceptingAttributes
   * @covers ::isRenderElementAcceptingAttributes
   *
   * @dataProvider providerTestAttributes
   */
  public function testIsAcceptingAttributes(array $element, bool $expected): void {
    $result = Element::isAcceptingAttributes($element);
    $this->assertSame($result, $expected);
  }

  /**
   * Data provider for testIsAcceptingAttributes().
   */
  public function providerTestAttributes() {
    $data = [
      'already_exist' => [['#attributes' => ['class' => ['original-class']]], TRUE],
      // isThemeHookAcceptingAttributes
      'theme_has_attributes' => [['#theme' => 'image', '#uri' => 'http://test.com/image.png'], TRUE],
      'theme_no_attributes' => [['#theme' => 'page_title', '#title' => 'My title'], FALSE],
      'not_existing_theme' => [['#theme' => 'not_existing_theme'], FALSE],
      'theme_base_hook' => [['#theme' => 'block__system_messages_block', 'block' => 'something'], TRUE],
      // @todo: find a #theme with template to test.
      // 'theme_template' => [['#theme' => 'entity_add_list', 'entity-add-list' => 'something'], TRUE],
      'invalid_theme' => [['#not_valid_theme' => ['class' => ['original-class']]], FALSE],
      // isRenderElementAcceptingAttributes second part
      'type_with_theme_valid' => [['#type' => 'html_tag', '#theme' => 'image', '#uri' => 'http://test.com/image.png'], TRUE],
      'type_with_theme_invalid' => [['#type' => 'html_tag', '#theme' => 'not_existing_theme'], FALSE],
      // isRenderElementAcceptingAttributes doCallback
      // @todo: find a #theme with #pre_render to test.
      // 'type_valid_callback' => [['#type' => 'not_with_or_without'], FALSE],
    ];

    // isRenderElementAcceptingAttributes first part.
    foreach (Element::$typeWithoutAttributes as $attributes) {
      $data['type_without_attributes_' . $attributes] = [['#type' => $attributes], FALSE];
    }
    foreach (Element::$typeWithAttributes as $attributes) {
      $data['type_with_attributes_' . $attributes] = [['#type' => $attributes], TRUE];
    }

    return $data;
  }
}
