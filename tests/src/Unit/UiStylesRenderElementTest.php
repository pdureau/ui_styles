<?php

declare(strict_types = 1);

namespace Drupal\Tests\ui_styles\Unit;

use Drupal\Core\Render\ElementInfoManager;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Theme\Registry;
use Drupal\Tests\UnitTestCase;
use Drupal\ui_styles\Render\Element;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Unit tests for UI Styles Render element.
 *
 * @group ui_styles
 *
 * @coversDefaultClass \Drupal\ui_styles\Render\Element
 */
class UiStylesRenderElementTest extends UnitTestCase {

  /**
   * The theme registry.
   *
   * @var \Drupal\Core\Theme\Registry|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $themeRegistry;

  /**
   * The element info plugin manager.
   *
   * @var \Drupal\Core\Render\ElementInfoManager|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $elementInfoManager;

  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  protected $container;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->container = new ContainerBuilder();

    $this->themeRegistry = $this->getMockBuilder(Registry::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['get'])
      ->getMock();

    $this->elementInfoManager = $this->getMockBuilder(ElementInfoManager::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getInfo'])
      ->getMock();

    $container = new ContainerBuilder();
    $container->set('theme.registry', $this->themeRegistry);
    $container->set('plugin.manager.element_info', $this->elementInfoManager);
    \Drupal::setContainer($container);
  }

  /**
   * Test addClasses().
   *
   * @covers ::addClasses
   */
  public function testAddClasses(): void {
    $element = [
      '#attributes' => [
        'class' => [
          'original-class',
        ],
      ],
    ];

    $elementMerged = Element::addClasses($element, ['added-class']);
    $this->assertContains('original-class', $elementMerged['#attributes']['class']);
    $this->assertContains('added-class', $elementMerged['#attributes']['class']);
  }

  /**
   * Test isAcceptingAttributes() when FALSE.
   *
   * @covers ::isAcceptingAttributes
   */
  public function testIsAcceptingAttributesReturnFalse(): void {
    $this->themeRegistry->expects($this->any())
      ->method('get')
      ->willReturn([
        'valid_theme' => [
          'variables' => ['attributes' => 'something'],
        ],
      ]);
    $element = [
      '#not_valid' => 'dummy',
    ];
    $result = Element::isAcceptingAttributes($element);
    $this->assertFalse($result, 'Element with no #theme or #type must be false');
  }

  /**
   * Test isThemeHookAcceptingAttributes() return FALSE.
   *
   * @covers ::isAcceptingAttributes
   * @covers ::isThemeHookAcceptingAttributes
   */
  public function testThemeIsAcceptingAttributesReturnFalse(): void {
    $this->themeRegistry->expects($this->any())
      ->method('get')
      ->willReturn([
        'valid_theme' => [
          'variables' => ['attributes' => 'something'],
        ],
      ]);
    $element = [
      '#theme' => 'not_valid_theme',
    ];
    $result = Element::isAcceptingAttributes($element);
    $this->assertFalse($result, 'Element with #theme not valid must be false');
  }

  /**
   * Test isThemeHookAcceptingAttributes().
   *
   * @covers ::isAcceptingAttributes
   * @covers ::isThemeHookAcceptingAttributes
   */
  public function testThemeIsAcceptingAttributes(): void {
    $this->themeRegistry->expects($this->any())
      ->method('get')
      ->willReturn([
        'valid_theme' => [
          'variables' => ['attributes' => 'something'],
        ],
      ]);
    $element = [
      '#theme' => 'valid_theme',
    ];
    $result = Element::isAcceptingAttributes($element);
    $this->assertTrue($result, 'Element with #theme valid must be true');
  }

  /**
   * Test isThemeHookAcceptingAttributes() with 'base hook'.
   *
   * @covers ::isAcceptingAttributes
   * @covers ::isThemeHookAcceptingAttributes
   */
  public function testThemeIsAcceptingAttributesBaseHook(): void {
    $this->themeRegistry->expects($this->once())
      ->method('get')
      ->willReturn([
        'valid_theme' => [
          'no_variables' => 'test',
          'base hook' => 'other_valid_theme',
        ],
        'other_valid_theme' => [
          'variables' => ['attributes' => 'something'],
        ],
      ]);
    $element = [
      '#theme' => 'valid_theme',
    ];
    $result = Element::isAcceptingAttributes($element);
    $this->assertTrue($result, 'Element with #theme valid from base hook must be true');
  }

  /**
   * Test isThemeHookAcceptingAttributes() with 'template'.
   *
   * @covers ::isAcceptingAttributes
   * @covers ::isThemeHookAcceptingAttributes
   */
  public function testThemeIsAcceptingAttributesTemplate(): void {
    foreach (Element::$themeWithAttributes as $attributes) {
      $this->themeRegistry->expects($this->any())
        ->method('get')
        ->willReturn([
          'valid_theme' => [
            'no_variables' => 'test',
            'template' => $attributes,
          ],
          'other_valid_theme' => [
            'variables' => ['attributes' => 'something'],
          ],
        ]);
      $element = [
        '#theme' => 'valid_theme',
      ];
      $result = Element::isAcceptingAttributes($element);
      $this->assertTrue($result, \sprintf('Element with #theme valid from template %s must be true', $attributes));
    }
  }

  /**
   * Test isRenderElementAcceptingAttributes() FALSE (without attributes).
   *
   * @covers ::isAcceptingAttributes
   * @covers ::isRenderElementAcceptingAttributes
   */
  public function testRenderIsAcceptingAttributesReturnFalse(): void {
    $element = [];
    foreach (Element::$typeWithoutAttributes as $attributes) {
      $element['#type'] = $attributes;
      $result = Element::isAcceptingAttributes($element);
      $this->assertFalse($result, \sprintf('Element #type %s must be false.', $attributes));
    }
  }

  /**
   * Test isRenderElementAcceptingAttributes() TRUE (with attributes).
   *
   * @covers ::isAcceptingAttributes
   * @covers ::isRenderElementAcceptingAttributes
   */
  public function testRenderIsAcceptingAttributesReturnTrue(): void {
    $element = [];
    foreach (Element::$typeWithAttributes as $attributes) {
      $element['#type'] = $attributes;
      $result = Element::isAcceptingAttributes($element);
      $this->assertTrue($result, \sprintf('Element #type %s must be true.', $attributes));
    }
  }

  /**
   * Test isRenderElementAcceptingAttributes() with #pre_render and doCallback.
   *
   * @covers ::doCallback
   * @covers ::isAcceptingAttributes
   * @covers ::isRenderElementAcceptingAttributes
   */
  public function testRenderIsAcceptingAttributesDoCallbackValid(): void {
    $this->themeRegistry->expects($this->any())
      ->method('get')
      ->willReturn([
        'valid_theme' => [
          'variables' => ['attributes' => 'something'],
        ],
      ]);
    $element = [
      '#type' => 'not_in_list',
    ];
    $this->elementInfoManager->expects($this->once())
      ->method('getInfo')
      ->willReturn(['#pre_render' => ['Drupal\Tests\ui_styles\Unit\DoCallbackTest::myCallbackValidTest']]);
    $result = Element::isAcceptingAttributes($element);
    $this->assertTrue($result, 'Element with #pre_render, #type not with/without and valid doCallback must be true.');
  }

  /**
   * Test isRenderElementAcceptingAttributes() with #pre_render and doCallback.
   *
   * @covers ::doCallback
   * @covers ::isAcceptingAttributes
   * @covers ::isRenderElementAcceptingAttributes
   */
  public function testRenderIsAcceptingAttributesDoCallbackNotValid(): void {
    $this->themeRegistry->expects($this->once())
      ->method('get')
      ->willReturn([
        'valid_theme' => [
          'variables' => ['attributes' => 'something'],
        ],
      ]);
    $element = [
      '#type' => 'not_in_list_not_valid',
    ];
    $this->elementInfoManager->expects($this->once())
      ->method('getInfo')
      ->willReturn(['#pre_render' => ['Drupal\Tests\ui_styles\Unit\DoCallbackTest::myCallbackNotValidTest']]);
    $result = Element::isAcceptingAttributes($element);
    $this->assertFalse($result, 'Element with #pre_render, #type not valid and not valid doCallback must be false.');
  }

  /**
   * Test isRenderElementAcceptingAttributes() with #pre_render and doCallback.
   *
   * @covers ::doCallback
   * @covers ::isAcceptingAttributes
   * @covers ::isRenderElementAcceptingAttributes
   */
  public function testRenderIsAcceptingAttributesDoCallbackNotValidTheme(): void {
    $element = [
      '#type' => 'not_in_list_not_valid_theme',
    ];
    $this->elementInfoManager->expects($this->once())
      ->method('getInfo')
      ->willReturn(['#pre_render' => ['Drupal\Tests\ui_styles\Unit\DoCallbackTest::myCallbackNotValidThemeTest']]);
    $result = Element::isAcceptingAttributes($element);
    $this->assertFalse($result, 'Element with #pre_render, #type not valid theme and not valid doCallback must be false.');
  }

}

/**
 * Dummy test class for doCallback.
 */
class DoCallbackTest implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks(): array {
    return [
      'myCallbackValidTest',
      'myCallbackNotValidTest',
      'myCallbackNotValidThemeTest',
    ];
  }

  /**
   * Test valid theme.
   */
  public static function myCallbackValidTest(): array {
    return ['#theme' => 'valid_theme'];
  }

  /**
   * Test not valid theme.
   */
  public static function myCallbackNotValidTest(): array {
    return ['#theme' => 'no_valid_theme'];
  }

  /**
   * Test not valid theme key.
   */
  public static function myCallbackNotValidThemeTest(): array {
    return ['#not_valid' => 'no_valid_theme'];
  }

}
