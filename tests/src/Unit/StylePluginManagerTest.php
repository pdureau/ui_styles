<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles\Unit;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Theme\Registry;
use Drupal\Tests\UnitTestCase;
use Drupal\ui_styles\Definition\StyleDefinition;
use Drupal\ui_styles\Source\SourcePluginManagerInterface;
use Drupal\ui_styles\StylePluginManager;
use Drupal\ui_styles_test\DummyStylePluginManager;
use Drupal\ui_styles_test\Plugin\UiStyles\Source\TestSelect;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Test the style plugin manager.
 *
 * @group ui_styles
 *
 * @coversDefaultClass \Drupal\ui_styles\StylePluginManager
 */
class StylePluginManagerTest extends UnitTestCase {

  /**
   * The container.
   *
   * @var \Symfony\Component\DependencyInjection\TaggedContainerInterface
   */
  protected $container;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected TranslationInterface $stringTranslation;

  /**
   * The Style plugin manager.
   *
   * @var \Drupal\ui_styles_test\DummyStylePluginManager
   */
  protected DummyStylePluginManager $stylePluginManager;

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
    // Needed for Element::isAcceptingAttributes.
    $this->container = new ContainerBuilder();

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
    $container = new ContainerBuilder();
    $container->set('theme.registry', $themeRegistry);
    \Drupal::setContainer($container);

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
    $this->stringTranslation = $this->getStringTranslationStub();

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

    $this->stylePluginManager = new DummyStylePluginManager($cache, $moduleHandler, $themeHandler, $transliteration, $sourcePluginManager, $this->stringTranslation);
    $this->stylePluginManager->setStyles($this->styles);
  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstructor(): void {
    $this->assertInstanceOf(
      StylePluginManager::class,
      $this->stylePluginManager
    );
  }

  /**
   * Tests the processDefinition().
   *
   * @covers ::processDefinition
   */
  public function testProcessDefinitionWillReturnException(): void {
    $plugin_id = 'test';
    $definition = ['no_id' => $plugin_id];
    try {
      $this->stylePluginManager->processDefinition($definition, $plugin_id);
    }
    catch (PluginException $exception) {
      $this->assertTrue(TRUE, 'The expected exception happened.');
    }
  }

  /**
   * Tests the processDefinition().
   *
   * @covers ::processDefinition
   */
  public function testProcessDefinition(): void {
    $plugin_id = 'test';
    $definition = ['id' => $plugin_id];

    $expected = new StyleDefinition($definition);
    $expected->setCategory($this->stringTranslation->translate('Other'));

    /** @var \Drupal\ui_styles\Definition\StyleDefinition $definition */
    $this->stylePluginManager->processDefinition($definition, $plugin_id);
    $this->assertInstanceOf(StyleDefinition::class, $definition);
    $this->assertEquals($definition->toArray(), $expected->toArray());
  }

  /**
   * @covers ::getCategories
   */
  public function testGetCategories(): void {
    $this->stylePluginManager->setStyles([
      'id_1' => [
        'id' => 'id_1',
        'category' => 'Cat 1',
      ],
      'id_2' => [
        'id' => 'id_2',
        'category' => 'Cat 2',
      ],
      'id_3' => [
        'id' => 'id_3',
      ],
    ]);
    $expected = [
      'Cat 1',
      'Cat 2',
      'Other',
    ];
    $categories = $this->stylePluginManager->getCategories();
    $this->assertEquals($expected, $categories);
  }

  /**
   * @covers ::getSortedDefinitions
   */
  public function testGetSortedDefinitions(): void {
    $this->stylePluginManager->setStyles([
      'id_1zz2' => [
        'weight' => 1,
        'category' => 'Z',
        'label' => '(Z)',
        'id' => 'id_1zz2',
      ],
      'id_1zz1' => [
        'weight' => 1,
        'category' => 'Z',
        'label' => 'Z',
        'id' => 'id_1zz1',
      ],
      'id_1za2' => [
        'weight' => 1,
        'category' => 'Z',
        'label' => '(A)',
        'id' => 'id_1za2',
      ],
      'id_1za1' => [
        'weight' => 1,
        'category' => 'Z',
        'label' => 'A',
        'id' => 'id_1za1',
      ],
      'id_1az2' => [
        'weight' => 1,
        'category' => 'A',
        'label' => '(Z)',
        'id' => 'id_1az2',
      ],
      'id_1az1' => [
        'weight' => 1,
        'category' => 'A',
        'label' => 'Z',
        'id' => 'id_1az1',
      ],
      'id_1aa2' => [
        'weight' => 1,
        'category' => 'A',
        'label' => '(A)',
        'id' => 'id_1aa2',
      ],
      'id_1aa1' => [
        'weight' => 1,
        'category' => 'A',
        'label' => 'A',
        'id' => 'id_1aa1',
      ],
      'id_0zz2' => [
        'weight' => 0,
        'category' => 'Z',
        'label' => '(Z)',
        'id' => 'id_0zz2',
      ],
      'id_0zz1' => [
        'weight' => 0,
        'category' => 'Z',
        'label' => 'Z',
        'id' => 'id_0zz1',
      ],
      'id_0za2' => [
        'weight' => 0,
        'category' => 'Z',
        'label' => '(A)',
        'id' => 'id_0za2',
      ],
      'id_0za1' => [
        'weight' => 0,
        'category' => 'Z',
        'label' => 'A',
        'id' => 'id_0za1',
      ],
      'id_0az2' => [
        'weight' => 0,
        'category' => 'A',
        'label' => '(Z)',
        'id' => 'id_0az2',
      ],
      'id_0az1' => [
        'weight' => 0,
        'category' => 'A',
        'label' => 'Z',
        'id' => 'id_0az1',
      ],
      'id_0aa2' => [
        'weight' => 0,
        'category' => 'A',
        'label' => '(A)',
        'id' => 'id_0aa2',
      ],
      'id_0aa1' => [
        'weight' => 0,
        'category' => 'A',
        'label' => 'A',
        'id' => 'id_0aa1',
      ],
    ]);

    $expected = [
      'id_0aa1',
      'id_0aa2',
      'id_0az1',
      'id_0az2',
      'id_0za1',
      'id_0za2',
      'id_0zz1',
      'id_0zz2',
      'id_1aa1',
      'id_1aa2',
      'id_1az1',
      'id_1az2',
      'id_1za1',
      'id_1za2',
      'id_1zz1',
      'id_1zz2',
    ];

    $sorted_definitions = $this->stylePluginManager->getSortedDefinitions();
    $this->assertEquals($expected, \array_keys($sorted_definitions));
    $this->assertContainsOnlyInstancesOf(StyleDefinition::class, $sorted_definitions);
  }

  /**
   * @covers ::getGroupedDefinitions
   */
  public function testGetGroupedDefinitions(): void {
    $this->stylePluginManager->setStyles([
      'cat_1_1_b' => [
        'id' => 'cat_1_1_b',
        'category' => 'Cat 1',
        'label' => 'B',
        'weight' => 1,
      ],
      'cat_1_1_a' => [
        'id' => 'cat_1_1_a',
        'category' => 'Cat 1',
        'label' => 'A',
        'weight' => 1,
      ],
      'cat_1_0_a' => [
        'id' => 'cat_1_0_a',
        'category' => 'Cat 1',
        'label' => 'A',
        'weight' => 0,
      ],
      'cat_2_0_a' => [
        'id' => 'cat_2_0_a',
        'category' => 'Cat 2',
        'label' => 'A',
        'weight' => 0,
      ],
      'no_category' => [
        'id' => 'no_category',
        'label' => 'B',
        'weight' => 0,
      ],
    ]);

    $category_expected = [
      'Cat 1' => [
        'cat_1_0_a',
        'cat_1_1_a',
        'cat_1_1_b',
      ],
      'Cat 2' => [
        'cat_2_0_a',
      ],
      'Other' => [
        'no_category',
      ],
    ];

    $definitions = $this->stylePluginManager->getGroupedDefinitions();
    $this->assertEquals(\array_keys($category_expected), \array_keys($definitions));
    foreach ($category_expected as $category => $expected) {
      $this->assertArrayHasKey($category, $definitions);
      $this->assertEquals($expected, \array_keys($definitions[$category]));
      $this->assertContainsOnlyInstancesOf(StyleDefinition::class, $definitions[$category]);
    }
  }

  /**
   * Test the alterForm().
   *
   * @covers ::alterForm
   */
  public function testAlterForm(): void {
    $form = [
      '#type' => 'details',
      '#title' => 'Main',
      '#open' => FALSE,
    ];

    // @phpstan-ignore-next-line
    $altered_form = $this->stylePluginManager->alterForm($form, [
      'test1' => 'opt2',
      'test2' => 'opt3',
    ], 'has_extra');
    $this->assertSame('has_extra', $altered_form['_ui_styles_extra']['#default_value']);
    $this->assertArrayHasKey('ui_styles_test1', $altered_form);
    $this->assertArrayHasKey('ui_styles_test2', $altered_form);
    $this->assertSame('opt2', $altered_form['ui_styles_test1']['#default_value']);
    $this->assertSame('opt3', $altered_form['ui_styles_test2']['#default_value']);
    $this->assertSame($this->styles[0]['options'], $altered_form['ui_styles_test1']['#options']);
    $this->assertSame($this->styles[0]['label'] . StylesElementTest::APPLIED_SUFFIX, $altered_form['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['options'], $altered_form['ui_styles_test2']['#options']);
    $this->assertSame($this->styles[1]['label'] . StylesElementTest::APPLIED_SUFFIX, $altered_form['ui_styles_test2']['#title']);
    $this->assertSame('Main' . StylesElementTest::APPLIED_SUFFIX, $altered_form['#title']);

    // Test that if no value is used suffix is not set.
    // @phpstan-ignore-next-line
    $altered_form = $this->stylePluginManager->alterForm($form, [], '');
    $this->assertArrayHasKey('ui_styles_test1', $altered_form);
    $this->assertArrayHasKey('ui_styles_test2', $altered_form);
    $this->assertSame($this->styles[0]['options'], $altered_form['ui_styles_test1']['#options']);
    $this->assertSame($this->styles[0]['label'], $altered_form['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['options'], $altered_form['ui_styles_test2']['#options']);
    $this->assertSame($this->styles[1]['label'], $altered_form['ui_styles_test2']['#title']);
  }

  /**
   * Test if applied suffix is correctly placed.
   *
   * @covers ::alterForm
   */
  public function testAppliedSuffix(): void {
    $form = [
      '#type' => 'details',
      '#title' => 'Main',
      '#open' => FALSE,
    ];

    $ungrouped_styles = [
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
    $this->stylePluginManager->setStyles($ungrouped_styles);

    // No values.
    // @phpstan-ignore-next-line
    $altered_form = $this->stylePluginManager->alterForm($form, [
      'test1' => '',
      'test2' => '',
    ], '');
    $this->assertArrayHasKey('ui_styles_test1', $altered_form);
    $this->assertArrayHasKey('ui_styles_test2', $altered_form);
    $this->assertSame('Main', $altered_form['#title']);
    $this->assertSame($this->styles[0]['label'], $altered_form['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['label'], $altered_form['ui_styles_test2']['#title']);

    // Value on test1.
    // @phpstan-ignore-next-line
    $altered_form = $this->stylePluginManager->alterForm($form, [
      'test1' => 'opt1',
      'test2' => '',
    ], '');
    $this->assertArrayHasKey('ui_styles_test1', $altered_form);
    $this->assertArrayHasKey('ui_styles_test2', $altered_form);
    $this->assertSame('Main' . StylesElementTest::APPLIED_SUFFIX, $altered_form['#title']);
    $this->assertSame($this->styles[0]['label'] . StylesElementTest::APPLIED_SUFFIX, $altered_form['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['label'], $altered_form['ui_styles_test2']['#title']);

    // Value on test2.
    // @phpstan-ignore-next-line
    $altered_form = $this->stylePluginManager->alterForm($form, [
      'test1' => '',
      'test2' => 'opt1',
    ], '');
    $this->assertArrayHasKey('ui_styles_test1', $altered_form);
    $this->assertArrayHasKey('ui_styles_test2', $altered_form);
    $this->assertSame('Main' . StylesElementTest::APPLIED_SUFFIX, $altered_form['#title']);
    $this->assertSame($this->styles[0]['label'], $altered_form['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['label'] . StylesElementTest::APPLIED_SUFFIX, $altered_form['ui_styles_test2']['#title']);

    // Value on extra.
    // @phpstan-ignore-next-line
    $altered_form = $this->stylePluginManager->alterForm($form, [
      'test1' => '',
      'test2' => '',
    ], 'extra');
    $this->assertArrayHasKey('ui_styles_test1', $altered_form);
    $this->assertArrayHasKey('ui_styles_test2', $altered_form);
    $this->assertSame('Main' . StylesElementTest::APPLIED_SUFFIX, $altered_form['#title']);
    $this->assertSame($this->styles[0]['label'], $altered_form['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['label'], $altered_form['ui_styles_test2']['#title']);

    $grouped_styles = [
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
    $this->stylePluginManager->setStyles($grouped_styles);

    // No values.
    // @phpstan-ignore-next-line
    $altered_form = $this->stylePluginManager->alterForm($form, [
      'test1' => '',
      'test2' => '',
    ], '');
    $this->assertArrayHasKey('main', $altered_form);
    $this->assertArrayHasKey('main_2', $altered_form);
    $this->assertArrayHasKey('ui_styles_test1', $altered_form['main']);
    $this->assertArrayHasKey('ui_styles_test2', $altered_form['main_2']);
    $this->assertSame('Main', $altered_form['#title']);
    $this->assertSame('Main', $altered_form['main']['#title']);
    $this->assertSame('Main 2', $altered_form['main_2']['#title']);
    $this->assertSame($this->styles[0]['label'], $altered_form['main']['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['label'], $altered_form['main_2']['ui_styles_test2']['#title']);

    // Value on test1.
    // @phpstan-ignore-next-line
    $altered_form = $this->stylePluginManager->alterForm($form, [
      'test1' => 'opt1',
      'test2' => '',
    ], '');
    $this->assertArrayHasKey('main', $altered_form);
    $this->assertArrayHasKey('main_2', $altered_form);
    $this->assertArrayHasKey('ui_styles_test1', $altered_form['main']);
    $this->assertArrayHasKey('ui_styles_test2', $altered_form['main_2']);
    $this->assertSame('Main' . StylesElementTest::APPLIED_SUFFIX, $altered_form['#title']);
    $this->assertSame('Main' . StylesElementTest::APPLIED_SUFFIX, $altered_form['main']['#title']);
    $this->assertSame('Main 2', $altered_form['main_2']['#title']);
    $this->assertSame($this->styles[0]['label'] . StylesElementTest::APPLIED_SUFFIX, $altered_form['main']['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['label'], $altered_form['main_2']['ui_styles_test2']['#title']);

    // Value on test2.
    // @phpstan-ignore-next-line
    $altered_form = $this->stylePluginManager->alterForm($form, [
      'test1' => '',
      'test2' => 'opt2',
    ], '');
    $this->assertArrayHasKey('main', $altered_form);
    $this->assertArrayHasKey('main_2', $altered_form);
    $this->assertArrayHasKey('ui_styles_test1', $altered_form['main']);
    $this->assertArrayHasKey('ui_styles_test2', $altered_form['main_2']);
    $this->assertSame('Main' . StylesElementTest::APPLIED_SUFFIX, $altered_form['#title']);
    $this->assertSame('Main', $altered_form['main']['#title']);
    $this->assertSame('Main 2' . StylesElementTest::APPLIED_SUFFIX, $altered_form['main_2']['#title']);
    $this->assertSame($this->styles[0]['label'], $altered_form['main']['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['label'] . StylesElementTest::APPLIED_SUFFIX, $altered_form['main_2']['ui_styles_test2']['#title']);

    // Value on extra.
    // @phpstan-ignore-next-line
    $altered_form = $this->stylePluginManager->alterForm($form, [
      'test1' => '',
      'test2' => '',
    ], 'extra');
    $this->assertArrayHasKey('main', $altered_form);
    $this->assertArrayHasKey('main_2', $altered_form);
    $this->assertArrayHasKey('ui_styles_test1', $altered_form['main']);
    $this->assertArrayHasKey('ui_styles_test2', $altered_form['main_2']);
    $this->assertSame('Main' . StylesElementTest::APPLIED_SUFFIX, $altered_form['#title']);
    $this->assertSame('Main', $altered_form['main']['#title']);
    $this->assertSame('Main 2', $altered_form['main_2']['#title']);
    $this->assertSame($this->styles[0]['label'], $altered_form['main']['ui_styles_test1']['#title']);
    $this->assertSame($this->styles[1]['label'], $altered_form['main_2']['ui_styles_test2']['#title']);
  }

  /**
   * Test the addClasses().
   *
   * @covers ::addClasses
   * @covers \Drupal\ui_styles\Render\Element::findFirstAcceptingAttributes
   * @covers \Drupal\ui_styles\Render\Element::wrapElementIfNotAcceptingAttributes
   */
  public function testAddClasses(): void {
    $element = [
      '#attributes' => [
        'class' => [
          'original-class',
        ],
      ],
    ];
    // Test no styles.
    $newElement = $this->stylePluginManager->addClasses($element);
    $this->assertContains('original-class', $newElement['#attributes']['class']);
    $this->assertNotContains('added-class', $newElement['#attributes']['class']);

    // Test Element::isAcceptingAttributes.
    $newElement = $this->stylePluginManager->addClasses($element, ['added-class'], 'extra-class');
    $this->assertContains('original-class', $newElement['#attributes']['class']);
    $this->assertContains('added-class', $newElement['#attributes']['class']);
    $this->assertContains('extra-class', $newElement['#attributes']['class']);

    // Test drilling process to set attributes on the first available element.
    // Classes must be set to all first-encountered elements in the tree
    // that allow attributes and not go deeper.
    // If root allows to add, it should not go deeper.
    // Test not able to add attributes and extra wrapper is added.
    $element = [
      '#no_attributes' => [
        'class' => [
          'original-class',
        ],
      ],
      'element1' => [
        '#no_attributes' => [],
        'element1_1' => [
          '#no_attributes' => [],
        ],
      ],
    ];
    $newElement = $this->stylePluginManager->addClasses($element, ['added-class'], 'extra-class');
    $this->assertContains('original-class', $newElement['element']['#no_attributes']['class']);
    $this->assertContains('added-class', $newElement['#attributes']['class']);
    $this->assertContains('extra-class', $newElement['#attributes']['class']);
    $this->assertEmpty($newElement['element']['element1']['#no_attributes']);
    $this->assertEmpty($newElement['element']['element1']['element1_1']['#no_attributes']);

    // Test complex structure with siblings.
    $element = [
      '#no_attributes' => [
        'class' => [
          'original-class',
        ],
      ],
      'element1' => [
        '#no_attributes' => [
          'class' => [
            'original-class',
          ],
        ],
        'element1_1' => [
          '#attributes' => [],
        ],
        'element1_2' => [
          '#attributes' => [],
          'element1_2_1' => [
            '#attributes' => [],
          ],
        ],
        'element1_3' => [
          '#no_attributes' => [
            'class' => [
              'original-class',
            ],
          ],
          'element1_3_1' => [
            '#attributes' => [],
          ],
        ],
      ],
      'element2' => [
        '#attributes' => [],
      ],
      'element3' => [
        '#no_attributes' => [],
      ],
    ];
    $newElement = $this->stylePluginManager->addClasses($element, ['added-class'], 'extra-class');
    // No wrapper added.
    $this->assertArrayNotHasKey('element', $newElement);
    $this->assertArrayNotHasKey('#attributes', $newElement);
    // Elements that must have classes added.
    $test_cases_must_have_parents = [
      ['element1', 'element1_1'],
      ['element1', 'element1_2'],
      ['element1', 'element1_3', 'element1_3_1'],
      ['element2'],
      ['element3'],
    ];
    foreach ($test_cases_must_have_parents as $parents) {
      /** @var array $nested_element */
      $nested_element = NestedArray::getValue($newElement, $parents);
      $this->assertContains('added-class', $nested_element['#attributes']['class']);
      $this->assertContains('extra-class', $nested_element['#attributes']['class']);
    }
    $this->assertArrayHasKey('element', $newElement['element3']);
    // Elements that must not have new classes added.
    $test_cases_not_have_parents = [
      ['element1', 'element1_2', 'element1_2_1'],
      ['element1', 'element1_3'],
    ];
    foreach ($test_cases_not_have_parents as $parents) {
      /** @var array $nested_element */
      $nested_element = NestedArray::getValue($newElement, $parents);
      $this->assertEmpty($nested_element['#attributes'] ?? NULL);
    }

    // Test addStyleToBlockContent > #theme:block > #theme:field.
    $element = [
      '#theme' => 'block',
      'content' => [
        '#theme' => 'field',
        '#formatter' => 'dummy',
        'test' => [
          // Allowed #attributes tag.
          '#type' => 'html_tag',
          '#attributes' => [
            'class' => ['original-class'],
          ],
        ],
      ],
    ];
    $newElement = $this->stylePluginManager->addClasses($element, ['added-class'], 'extra-class');
    $this->assertContains('original-class', $newElement['content']['test']['#attributes']['class']);
    $this->assertContains('added-class', $newElement['content']['test']['#attributes']['class']);
    $this->assertContains('extra-class', $newElement['content']['test']['#attributes']['class']);

    // Test addStyleToBlockContent > #theme:block > #theme:field
    // > media_thumbnail.
    $element = [
      '#theme' => 'block',
      'content' => [
        '#theme' => 'field',
        '#formatter' => 'media_thumbnail',
        'test' => [
          // Allowed #attributes tag.
          '#theme' => 'image_formatter',
          '#item_attributes' => [
            'class' => ['original-class'],
          ],
        ],
      ],
    ];
    $newElement = $this->stylePluginManager->addClasses($element, ['added-class'], 'extra-class');
    $this->assertContains('original-class', $newElement['content']['test']['#item_attributes']['class']);
    $this->assertContains('added-class', $newElement['content']['test']['#item_attributes']['class']);
    $this->assertContains('extra-class', $newElement['content']['test']['#item_attributes']['class']);

    // Test addStyleToBlockContent > #theme:block > #theme:field
    // > !isAcceptingAttributes.
    $element = [
      '#theme' => 'block',
      'content' => [
        '#theme' => 'field',
        '#formatter' => 'dummy',
        'test' => [
          // Not allowed #attributes tag.
          '#type' => 'inline_template',
          '#no_attributes' => [
            'class' => ['original-class'],
          ],
        ],
      ],
    ];
    $newElement = $this->stylePluginManager->addClasses($element, ['added-class'], 'extra-class');
    // The content had been wrapped in a div.
    $this->assertContains('original-class', $newElement['content']['test']['element']['#no_attributes']['class']);
    $this->assertNotContains('added-class', $newElement['content']['test']['element']['#no_attributes']['class']);
    $this->assertNotContains('extra-class', $newElement['content']['test']['element']['#no_attributes']['class']);
    $this->assertContains('added-class', $newElement['content']['test']['#attributes']['class']);
    $this->assertContains('extra-class', $newElement['content']['test']['#attributes']['class']);

    // Test addStyleToBlockContent > #theme:block > #theme:dummy
    // > !isAcceptingAttributes.
    $element = [
      '#theme' => 'block',
      'content' => [
        '#theme' => 'dummy',
        'test' => [
          // Not allowed #attributes tag.
          '#type' => 'inline_template',
          '#no_attributes' => [
            'class' => ['original-class'],
          ],
        ],
      ],
    ];
    $newElement = $this->stylePluginManager->addClasses($element, ['added-class'], 'extra-class');
    // The content had been wrapped in a div.
    $this->assertContains('original-class', $newElement['content']['element']['test']['#no_attributes']['class']);
    $this->assertNotContains('added-class', $newElement['content']['element']['test']['#no_attributes']['class']);
    $this->assertNotContains('extra-class', $newElement['content']['element']['test']['#no_attributes']['class']);
    $this->assertContains('added-class', $newElement['content']['#attributes']['class']);
    $this->assertContains('extra-class', $newElement['content']['#attributes']['class']);

    // Test addStyleToBlockContent > #theme:block > #theme:not field.
    $element = [
      '#theme' => 'dummy',
      '#attributes' => [
        'class' => ['original-class'],
      ],
    ];
    $newElement = $this->stylePluginManager->addClasses($element, ['added-class'], 'extra-class');
    $this->assertContains('original-class', $newElement['#attributes']['class']);
    $this->assertContains('added-class', $newElement['#attributes']['class']);
    $this->assertContains('extra-class', $newElement['#attributes']['class']);

    // Test addStyleToBlockContent > #theme:block > no content.
    $element = [
      '#theme' => 'block',
      'content' => [],
      '#attributes' => [
        'class' => ['original-class'],
      ],
    ];
    $newElement = $this->stylePluginManager->addClasses($element, ['added-class'], 'extra-class');
    $this->assertContains('original-class', $newElement['#attributes']['class']);
    $this->assertContains('added-class', $newElement['#attributes']['class']);
    $this->assertContains('extra-class', $newElement['#attributes']['class']);

    // Test addStyleToBlockContent > #view_mode > _layout_builder.
    $element = [
      '#theme' => 'block',
      'content' => [
        '#view_mode' => 'block',
        '_layout_builder' => [
          [
            // Allowed #attributes tag.
            '#type' => 'html_tag',
            '#attributes' => [
              'class' => ['original-class'],
            ],
          ],
          [
            // Allowed #attributes tag.
            '#type' => 'html_tag',
            '#attributes' => [
              'class' => ['original-class'],
            ],
          ],
        ],
      ],
    ];
    $newElement = $this->stylePluginManager->addClasses($element, ['added-class'], 'extra-class');
    $this->assertContains('original-class', $newElement['content']['_layout_builder'][0]['#attributes']['class']);
    $this->assertContains('added-class', $newElement['content']['_layout_builder'][0]['#attributes']['class']);
    $this->assertContains('extra-class', $newElement['content']['_layout_builder'][0]['#attributes']['class']);
    $this->assertContains('original-class', $newElement['content']['_layout_builder'][1]['#attributes']['class']);
    $this->assertContains('added-class', $newElement['content']['_layout_builder'][1]['#attributes']['class']);
    $this->assertContains('extra-class', $newElement['content']['_layout_builder'][1]['#attributes']['class']);

    // Test addStyleToBlockContent > #view_mode > no _layout_builder.
    $element = [
      '#theme' => 'block',
      'content' => [
        '#view_mode' => 'block',
        '_no_layout_builder' => [
          0 => [
            // Allowed #attributes tag.
            '#type' => 'inline_template',
            '#attributes' => [
              'class' => ['original-class'],
            ],
          ],
        ],
        '_layout_builder' => [
          '#cache' => [
            'contexts' => ['my_context'],
            'tags' => ['my_tag'],
            'max-age' => -1,
          ],
        ],
      ],
    ];
    $newElement = $this->stylePluginManager->addClasses($element, ['added-class'], 'extra-class');
    $this->assertContains('original-class', $newElement['content']['_no_layout_builder'][0]['#attributes']['class']);
    $this->assertContains('added-class', $newElement['content']['_no_layout_builder'][0]['#attributes']['class']);
    $this->assertContains('extra-class', $newElement['content']['_no_layout_builder'][0]['#attributes']['class']);
    $this->assertCount(1, $newElement['content']['_layout_builder']);

    // Test addStyleToBlockContent > no #theme : no #view_mode
    // > isAcceptingAttributes.
    $element = [
      '#theme' => 'block',
      'content' => [
        // Allowed #attributes tag.
        '#type' => 'html_tag',
        '#attributes' => [
          'class' => ['original-class'],
        ],
      ],
      '#attributes' => [
        'class' => ['original-class'],
      ],
    ];
    $newElement = $this->stylePluginManager->addClasses($element, ['added-class'], 'extra-class');
    $this->assertContains('original-class', $newElement['content']['#attributes']['class']);
    $this->assertContains('added-class', $newElement['content']['#attributes']['class']);
    $this->assertContains('extra-class', $newElement['content']['#attributes']['class']);

    // Test addStyleToBlockContent > no #theme : no #view_mode
    // > !isAcceptingAttributes.
    $element = [
      '#theme' => 'block',
      'content' => [
        // Not allowed #attributes tag.
        '#type' => 'inline_template',
        '#attributes' => [
          'class' => ['original-class'],
        ],
      ],
      '#attributes' => [
        'class' => ['original-class'],
      ],
    ];
    $newElement = $this->stylePluginManager->addClasses($element, ['added-class'], 'extra-class');
    $this->assertContains('original-class', $newElement['content']['#attributes']['class']);
    $this->assertContains('added-class', $newElement['content']['#attributes']['class']);
    $this->assertContains('extra-class', $newElement['content']['#attributes']['class']);

    // Test addStyleToBlockContent > #theme:block > #theme:field.
    $element = [
      '#theme' => 'block',
      'content' => [
        '#theme' => 'field',
        '#formatter' => 'dummy',
        'test' => [
          // Not allowed #attributes tag.
          '#type' => 'inline_template',
          '#attributes' => [
            'class' => ['original-class'],
          ],
        ],
      ],
    ];
    $newElement = $this->stylePluginManager->addClasses($element, ['added-class'], 'extra-class');
    $this->assertContains('original-class', $newElement['content']['test']['#attributes']['class']);
    $this->assertContains('added-class', $newElement['content']['test']['#attributes']['class']);
    $this->assertContains('extra-class', $newElement['content']['test']['#attributes']['class']);

    // Test addStyleToBlockContent > #theme:block > #theme:field
    // > image_formatter.
    $element = [
      '#theme' => 'block',
      'content' => [
        '#theme' => 'field',
        '#formatter' => 'dummy',
        'test_image_formatter' => [
          '#theme' => 'image_formatter',
          '#item_attributes' => [
            'class' => ['original-class'],
          ],
        ],
      ],
    ];
    $newElement = $this->stylePluginManager->addClasses($element, ['added-class'], 'extra-class');
    $this->assertContains('original-class', $newElement['content']['test_image_formatter']['#item_attributes']['class']);
    $this->assertContains('added-class', $newElement['content']['test_image_formatter']['#item_attributes']['class']);
    $this->assertContains('extra-class', $newElement['content']['test_image_formatter']['#item_attributes']['class']);

    // Test addStyleToBlockContent > #theme:block > #theme:field
    // > responsive_image_formatter.
    $element = [
      '#theme' => 'block',
      'content' => [
        '#theme' => 'field',
        '#formatter' => 'dummy',
        'test_responsive_image_formatter' => [
          '#theme' => 'responsive_image_formatter',
          '#item_attributes' => [
            'class' => ['original-class'],
          ],
        ],
      ],
    ];
    $newElement = $this->stylePluginManager->addClasses($element, ['added-class'], 'extra-class');
    $this->assertContains('original-class', $newElement['content']['test_responsive_image_formatter']['#item_attributes']['class']);
    $this->assertContains('added-class', $newElement['content']['test_responsive_image_formatter']['#item_attributes']['class']);
    $this->assertContains('extra-class', $newElement['content']['test_responsive_image_formatter']['#item_attributes']['class']);
  }

}
