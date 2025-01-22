<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles\Unit;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Theme\Registry;
use Drupal\Tests\UnitTestCase;
use Drupal\ui_styles\Element\Styles;
use Drupal\ui_styles\Source\SourcePluginManagerInterface;
use Drupal\ui_styles_test\DummyStylePluginManager;
use Drupal\ui_styles_test\Plugin\UiStyles\Source\TestSelect;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @coversDefaultClass \Drupal\ui_styles\Element\Styles
 *
 * @group ui_styles
 */
class StylesElementTest extends UnitTestCase {

  public const APPLIED_SUFFIX = ' <sup>(<mark>applied</mark>)</sup>';

  /**
   * A list of styles definitions.
   *
   * @var array
   */
  protected $styles = [
    0 => [
      'id' => 'test1',
      'category' => 'Main',
      'options' => ['opt1', 'opt2', 'opt3'],
      'label' => 'has_label',
    ],
    1 => [
      'id' => 'test2',
      'category' => 'Main',
      'options' => ['opt1', 'opt2', 'opt3'],
      'label' => 'has_label',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $container = new ContainerBuilder();

    $themeRegistry = $this->createMock(Registry::class);
    $themeRegistry->expects($this->any())
      ->method('get')
      ->willReturn([
        'valid_theme' => [
          'variables' => ['attributes' => 'something'],
        ],
        'block' => [
          'variables' => [],
          'template' => 'block',
        ],
      ]);
    $container->set('theme.registry', $themeRegistry);

    // Set up for this class.
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject $moduleHandler */
    $moduleHandler = $this->createMock(ModuleHandlerInterface::class);
    $moduleHandler->expects($this->any())
      ->method('getModuleDirectories')
      ->willReturn([]);

    /** @var \Drupal\Core\Extension\ThemeHandlerInterface|\PHPUnit\Framework\MockObject\MockObject $themeHandler */
    $themeHandler = $this->createMock(ThemeHandlerInterface::class);
    $themeHandler->expects($this->any())
      ->method('getThemeDirectories')
      ->willReturn([]);

    $cache = $this->createMock(CacheBackendInterface::class);
    $stringTranslation = $this->getStringTranslationStub();
    $container->set('string_translation', $stringTranslation);

    $transliteration = $this->createMock(TransliterationInterface::class);
    $transliteration->expects($this->any())
      ->method('transliterate')
      ->willReturnCallback(static function (string $string) {
        switch ($string) {
          case 'Main':
            return 'Main';

          case 'Main 2':
            return 'Main 2';
        }
        return '';
      });
    $container->set('transliteration', $transliteration);

    $sourcePluginManager = $this->createMock(SourcePluginManagerInterface::class);
    $sourcePluginManager->expects($this->any())
      ->method('getApplicableSourcePlugin')
      ->willReturn(new TestSelect(
        [],
        'test_select',
        [
          'id' => 'test_select',
        ]
      ));
    $container->set('plugin.manager.ui_styles.source', $sourcePluginManager);

    $stylePluginManager = new DummyStylePluginManager($cache, $moduleHandler, $themeHandler, $transliteration, $sourcePluginManager, $stringTranslation);
    $stylePluginManager->setStyles($this->styles);
    $container->set('plugin.manager.ui_styles', $stylePluginManager);

    \Drupal::setContainer($container);
  }

  /**
   * Test the buildForm().
   *
   * @covers ::buildForm
   */
  public function testBuildForm(): void {
    $formState = new FormState();
    $completeForm = [];

    $element = [
      '#type' => 'ui_styles_styles',
      '#title' => 'Main',
      '#default_value' => [
        'selected' => [
          'test1' => 'opt2',
          'test2' => 'opt3',
        ],
        'extra' => 'has_extra',
      ],
    ];
    $processedElement = Styles::buildForm($element, $formState, $completeForm);
    $this->assertSame('has_extra', $processedElement['wrapper']['_ui_styles_extra']['#default_value']);
    $this->assertArrayHasKey('ui_styles_test1', $processedElement['wrapper']);
    $this->assertArrayHasKey('ui_styles_test2', $processedElement['wrapper']);
    $this->assertSame('opt2', $processedElement['wrapper']['ui_styles_test1']['#default_value']);
    $this->assertSame('opt3', $processedElement['wrapper']['ui_styles_test2']['#default_value']);
    $this->assertSame($this->styles[0]['options'], $processedElement['wrapper']['ui_styles_test1']['#options']);
    $this->assertSame($this->styles[0]['label'] . static::APPLIED_SUFFIX, $processedElement['wrapper']['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['options'], $processedElement['wrapper']['ui_styles_test2']['#options']);
    $this->assertSame($this->styles[1]['label'] . static::APPLIED_SUFFIX, $processedElement['wrapper']['ui_styles_test2']['#title']);
    $this->assertSame('Main' . static::APPLIED_SUFFIX, $processedElement['wrapper']['#title']);

    // Test that if no value is used suffix is not set.
    $element = [
      '#type' => 'ui_styles_styles',
      '#title' => 'Main',
    ];
    $processedElement = Styles::buildForm($element, $formState, $completeForm);
    $this->assertArrayHasKey('ui_styles_test1', $processedElement['wrapper']);
    $this->assertArrayHasKey('ui_styles_test2', $processedElement['wrapper']);
    $this->assertSame($this->styles[0]['options'], $processedElement['wrapper']['ui_styles_test1']['#options']);
    $this->assertSame($this->styles[0]['label'], $processedElement['wrapper']['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['options'], $processedElement['wrapper']['ui_styles_test2']['#options']);
    $this->assertSame($this->styles[1]['label'], $processedElement['wrapper']['ui_styles_test2']['#title']);
  }

  /**
   * Test if applied suffix is correctly placed.
   *
   * @covers ::buildForm
   * @covers ::getAppliedSuffix
   */
  public function testAppliedSuffix(): void {
    $formState = new FormState();
    $completeForm = [];
    /** @var \Drupal\ui_styles_test\DummyStylePluginManager $stylesManager */
    $stylesManager = \Drupal::service('plugin.manager.ui_styles');

    $ungroupedStyles = [
      0 => [
        'id' => 'test1',
        'category' => 'Main',
        'options' => ['opt1', 'opt2', 'opt3'],
        'label' => 'has_label',
      ],
      1 => [
        'id' => 'test2',
        'category' => 'Main',
        'options' => ['opt1', 'opt2', 'opt3'],
        'label' => 'has_label',
      ],
    ];
    $stylesManager->setStyles($ungroupedStyles);

    // No values.
    $element = [
      '#type' => 'ui_styles_styles',
      '#title' => 'Main',
      '#default_value' => [
        'selected' => [
          'test1' => '',
          'test2' => '',
        ],
      ],
    ];
    $processedElement = Styles::buildForm($element, $formState, $completeForm);
    $this->assertArrayHasKey('ui_styles_test1', $processedElement['wrapper']);
    $this->assertArrayHasKey('ui_styles_test2', $processedElement['wrapper']);
    $this->assertSame('Main', $processedElement['wrapper']['#title']);
    $this->assertSame($this->styles[0]['label'], $processedElement['wrapper']['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['label'], $processedElement['wrapper']['ui_styles_test2']['#title']);

    // Value on test1.
    $element = [
      '#type' => 'ui_styles_styles',
      '#title' => 'Main',
      '#default_value' => [
        'selected' => [
          'test1' => 'opt1',
          'test2' => '',
        ],
      ],
    ];
    $processedElement = Styles::buildForm($element, $formState, $completeForm);
    $this->assertArrayHasKey('ui_styles_test1', $processedElement['wrapper']);
    $this->assertArrayHasKey('ui_styles_test2', $processedElement['wrapper']);
    $this->assertSame('Main' . static::APPLIED_SUFFIX, $processedElement['wrapper']['#title']);
    $this->assertSame($this->styles[0]['label'] . static::APPLIED_SUFFIX, $processedElement['wrapper']['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['label'], $processedElement['wrapper']['ui_styles_test2']['#title']);

    // Value on test2.
    $element = [
      '#type' => 'ui_styles_styles',
      '#title' => 'Main',
      '#default_value' => [
        'selected' => [
          'test1' => '',
          'test2' => 'opt1',
        ],
      ],
    ];
    $processedElement = Styles::buildForm($element, $formState, $completeForm);
    $this->assertArrayHasKey('ui_styles_test1', $processedElement['wrapper']);
    $this->assertArrayHasKey('ui_styles_test2', $processedElement['wrapper']);
    $this->assertSame('Main' . static::APPLIED_SUFFIX, $processedElement['wrapper']['#title']);
    $this->assertSame($this->styles[0]['label'], $processedElement['wrapper']['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['label'] . static::APPLIED_SUFFIX, $processedElement['wrapper']['ui_styles_test2']['#title']);

    // Value on extra.
    $element = [
      '#type' => 'ui_styles_styles',
      '#title' => 'Main',
      '#default_value' => [
        'selected' => [
          'test1' => '',
          'test2' => '',
        ],
        'extra' => 'extra',
      ],
    ];
    $processedElement = Styles::buildForm($element, $formState, $completeForm);
    $this->assertArrayHasKey('ui_styles_test1', $processedElement['wrapper']);
    $this->assertArrayHasKey('ui_styles_test2', $processedElement['wrapper']);
    $this->assertSame('Main' . static::APPLIED_SUFFIX, $processedElement['wrapper']['#title']);
    $this->assertSame($this->styles[0]['label'], $processedElement['wrapper']['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['label'], $processedElement['wrapper']['ui_styles_test2']['#title']);

    $groupedStyles = [
      0 => [
        'id' => 'test1',
        'category' => 'Main',
        'options' => ['opt1', 'opt2', 'opt3'],
        'label' => 'has_label',
      ],
      1 => [
        'id' => 'test2',
        'category' => 'Main 2',
        'options' => ['opt1', 'opt2', 'opt3'],
        'label' => 'has_label',
      ],
    ];
    $stylesManager->setStyles($groupedStyles);

    // No values.
    $element = [
      '#type' => 'ui_styles_styles',
      '#title' => 'Main',
      '#default_value' => [
        'selected' => [
          'test1' => '',
          'test2' => '',
        ],
      ],
    ];
    $processedElement = Styles::buildForm($element, $formState, $completeForm);
    $this->assertArrayHasKey('main', $processedElement['wrapper']);
    $this->assertArrayHasKey('main_2', $processedElement['wrapper']);
    $this->assertArrayHasKey('ui_styles_test1', $processedElement['wrapper']['main']);
    $this->assertArrayHasKey('ui_styles_test2', $processedElement['wrapper']['main_2']);
    $this->assertSame('Main', $processedElement['wrapper']['#title']);
    $this->assertSame('Main', $processedElement['wrapper']['main']['#title']);
    $this->assertSame('Main 2', $processedElement['wrapper']['main_2']['#title']);
    $this->assertSame($this->styles[0]['label'], $processedElement['wrapper']['main']['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['label'], $processedElement['wrapper']['main_2']['ui_styles_test2']['#title']);

    // Value on test1.
    $element = [
      '#type' => 'ui_styles_styles',
      '#title' => 'Main',
      '#default_value' => [
        'selected' => [
          'test1' => 'opt1',
          'test2' => '',
        ],
      ],
    ];
    $processedElement = Styles::buildForm($element, $formState, $completeForm);
    $this->assertArrayHasKey('main', $processedElement['wrapper']);
    $this->assertArrayHasKey('main_2', $processedElement['wrapper']);
    $this->assertArrayHasKey('ui_styles_test1', $processedElement['wrapper']['main']);
    $this->assertArrayHasKey('ui_styles_test2', $processedElement['wrapper']['main_2']);
    $this->assertSame('Main' . static::APPLIED_SUFFIX, $processedElement['wrapper']['#title']);
    $this->assertSame('Main' . static::APPLIED_SUFFIX, $processedElement['wrapper']['main']['#title']);
    $this->assertSame('Main 2', $processedElement['wrapper']['main_2']['#title']);
    $this->assertSame($this->styles[0]['label'] . static::APPLIED_SUFFIX, $processedElement['wrapper']['main']['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['label'], $processedElement['wrapper']['main_2']['ui_styles_test2']['#title']);

    // Value on test2.
    $element = [
      '#type' => 'ui_styles_styles',
      '#title' => 'Main',
      '#default_value' => [
        'selected' => [
          'test1' => '',
          'test2' => 'opt2',
        ],
      ],
    ];
    $processedElement = Styles::buildForm($element, $formState, $completeForm);
    $this->assertArrayHasKey('main', $processedElement['wrapper']);
    $this->assertArrayHasKey('main_2', $processedElement['wrapper']);
    $this->assertArrayHasKey('ui_styles_test1', $processedElement['wrapper']['main']);
    $this->assertArrayHasKey('ui_styles_test2', $processedElement['wrapper']['main_2']);
    $this->assertSame('Main' . static::APPLIED_SUFFIX, $processedElement['wrapper']['#title']);
    $this->assertSame('Main', $processedElement['wrapper']['main']['#title']);
    $this->assertSame('Main 2' . static::APPLIED_SUFFIX, $processedElement['wrapper']['main_2']['#title']);
    $this->assertSame($this->styles[0]['label'], $processedElement['wrapper']['main']['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['label'] . static::APPLIED_SUFFIX, $processedElement['wrapper']['main_2']['ui_styles_test2']['#title']);

    // Value on extra.
    $element = [
      '#type' => 'ui_styles_styles',
      '#title' => 'Main',
      '#default_value' => [
        'selected' => [
          'test1' => '',
          'test2' => '',
        ],
        'extra' => 'extra',
      ],
    ];
    $processedElement = Styles::buildForm($element, $formState, $completeForm);
    $this->assertArrayHasKey('main', $processedElement['wrapper']);
    $this->assertArrayHasKey('main_2', $processedElement['wrapper']);
    $this->assertArrayHasKey('ui_styles_test1', $processedElement['wrapper']['main']);
    $this->assertArrayHasKey('ui_styles_test2', $processedElement['wrapper']['main_2']);
    $this->assertSame('Main' . static::APPLIED_SUFFIX, $processedElement['wrapper']['#title']);
    $this->assertSame('Main', $processedElement['wrapper']['main']['#title']);
    $this->assertSame('Main 2', $processedElement['wrapper']['main_2']['#title']);
    $this->assertSame($this->styles[0]['label'], $processedElement['wrapper']['main']['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['label'], $processedElement['wrapper']['main_2']['ui_styles_test2']['#title']);
  }

  /**
   * Test valueCallback.
   *
   * @param mixed $expected
   *   The expected return depending on the input.
   * @param mixed $input
   *   The input.
   *
   * @covers ::valueCallback
   *
   * @dataProvider providerTestValueCallback
   */
  public function testValueCallback($expected, $input): void {
    $element = [];
    $form_state = new FormState();
    $this->assertSame($expected, Styles::valueCallback($element, ['wrapper' => $input], $form_state));
  }

  /**
   * Data provider for testValueCallback().
   */
  public static function providerTestValueCallback(): array {
    $data = [];
    $data['nominal_no_group'] = [[
      'selected' => [
        'test' => 'test',
      ],
      'extra' => 'extra',
    ], [
      'ui_styles_test' => 'test',
      '_ui_styles_extra' => 'extra',
    ],
    ];
    $data['nominal_with_group'] = [[
      'selected' => [
        'test' => 'test',
      ],
      'extra' => 'extra',
    ], [
      'group' => [
        'ui_styles_test' => 'test',
      ],
      '_ui_styles_extra' => 'extra',
    ],
    ];
    $data['empty'] = [[], [
      'group' => [
        'ui_styles_test' => '',
      ],
      '_ui_styles_extra' => '',
    ],
    ];
    $data['empty_selected'] = [[
      'extra' => 'extra',
    ], [
      'group' => [
        'ui_styles_test' => '',
      ],
      '_ui_styles_extra' => 'extra',
    ],
    ];
    $data['empty_extra'] = [[
      'selected' => [
        'test' => 'test',
      ],
    ], [
      'group' => [
        'ui_styles_test' => 'test',
      ],
      '_ui_styles_extra' => '',
    ],
    ];

    return $data;
  }

}
