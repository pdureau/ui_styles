<?php

declare(strict_types = 1);

namespace Drupal\Tests\ui_styles\Unit;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Theme\Registry;
use Drupal\Tests\UnitTestCase;
use Drupal\ui_styles\Definition\StyleDefinition;
use Drupal\ui_styles\StylePluginManager;
use Drupal\ui_styles_test\DummyStylePluginManager;
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
      'options' => ['opt1', 'opt2', 'opt3'],
      'label' => 'has_label',
    ],
    1 => [
      'id' => 'test2',
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

    $this->stylePluginManager = new DummyStylePluginManager($cache, $moduleHandler, $themeHandler, $transliteration, $this->stringTranslation);
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
      'id_z1z2' => [
        'category' => 'Z',
        'weight' => 1,
        'label' => '(Z)',
        'id' => 'id_z1z2',
      ],
      'id_z1z1' => [
        'category' => 'Z',
        'weight' => 1,
        'label' => 'Z',
        'id' => 'id_z1z1',
      ],
      'id_z1a2' => [
        'category' => 'Z',
        'weight' => 1,
        'label' => '(A)',
        'id' => 'id_z1a2',
      ],
      'id_z1a1' => [
        'category' => 'Z',
        'weight' => 1,
        'label' => 'A',
        'id' => 'id_z1a1',
      ],
      'id_z0z2' => [
        'category' => 'Z',
        'weight' => 0,
        'label' => '(Z)',
        'id' => 'id_z0z2',
      ],
      'id_z0z1' => [
        'category' => 'Z',
        'weight' => 0,
        'label' => 'Z',
        'id' => 'id_z0z1',
      ],
      'id_z0a2' => [
        'category' => 'Z',
        'weight' => 0,
        'label' => '(A)',
        'id' => 'id_z0a2',
      ],
      'id_z0a1' => [
        'category' => 'Z',
        'weight' => 0,
        'label' => 'A',
        'id' => 'id_z0a1',
      ],
      'id_a1z2' => [
        'category' => 'A',
        'weight' => 1,
        'label' => '(Z)',
        'id' => 'id_a1z2',
      ],
      'id_a1z1' => [
        'category' => 'A',
        'weight' => 1,
        'label' => 'Z',
        'id' => 'id_a1z1',
      ],
      'id_a1a2' => [
        'category' => 'A',
        'weight' => 1,
        'label' => '(A)',
        'id' => 'id_a1a2',
      ],
      'id_a1a1' => [
        'category' => 'A',
        'weight' => 1,
        'label' => 'A',
        'id' => 'id_a1a1',
      ],
      'id_a0z2' => [
        'category' => 'A',
        'weight' => 0,
        'label' => '(Z)',
        'id' => 'id_a0z2',
      ],
      'id_a0z1' => [
        'category' => 'A',
        'weight' => 0,
        'label' => 'Z',
        'id' => 'id_a0z1',
      ],
      'id_a0a2' => [
        'category' => 'A',
        'weight' => 0,
        'label' => '(A)',
        'id' => 'id_a0a2',
      ],
      'id_a0a1' => [
        'category' => 'A',
        'weight' => 0,
        'label' => 'A',
        'id' => 'id_a0a1',
      ],
    ]);

    $expected = [
      'id_a0a1',
      'id_a0a2',
      'id_a0z1',
      'id_a0z2',
      'id_a1a1',
      'id_a1a2',
      'id_a1z1',
      'id_a1z2',
      'id_z0a1',
      'id_z0a2',
      'id_z0z1',
      'id_z0z2',
      'id_z1a1',
      'id_z1a2',
      'id_z1z1',
      'id_z1z2',
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
        'id' => 'cat_1_0_a',
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
    $form = [];
    $extra = 'has_extra';

    $form = $this->stylePluginManager->alterForm($form, [
      'test1' => 'opt2',
      'test2' => 'opt3',
    ], $extra);

    $this->assertSame($form['_ui_styles_extra']['#default_value'], 'has_extra');
    $this->assertArrayHasKey('ui_styles_test1', $form);
    $this->assertArrayHasKey('ui_styles_test2', $form);
    $this->assertSame($form['ui_styles_test1']['#default_value'], 'opt2');
    $this->assertSame($form['ui_styles_test2']['#default_value'], 'opt3');
    $this->assertSame($form['ui_styles_test1']['#options'], $this->styles[0]['options']);
    $this->assertSame($form['ui_styles_test1']['#title'], $this->styles[0]['label']);
    $this->assertSame($form['ui_styles_test2']['#options'], $this->styles[1]['options']);
    $this->assertSame($form['ui_styles_test2']['#title'], $this->styles[1]['label']);
  }

  /**
   * Test the addClasses().
   *
   * @covers ::addClasses
   * @covers ::addStyleToBlockContent
   * @covers ::addStyleToFieldFormatterItems
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

    // Test not able to add attributes.
    $element = [
      '#no_attributes' => [
        'class' => [
          'original-class',
        ],
      ],
    ];
    $newElement = $this->stylePluginManager->addClasses($element, ['added-class'], 'extra-class');
    $this->assertContains('original-class', $newElement['element']['#no_attributes']['class']);
    $this->assertContains('added-class', $newElement['#attributes']['class']);
    $this->assertContains('extra-class', $newElement['#attributes']['class']);

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
          '#type' => 'html_tag',
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
        '#formatter' => 'media_thumbnail',
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
    $this->assertContains('added-class', $newElement['content']['test']['#item_attributes']['class']);
    $this->assertContains('extra-class', $newElement['content']['test']['#item_attributes']['class']);

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
          0 => [
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
      ],
    ];
    $newElement = $this->stylePluginManager->addClasses($element, ['added-class'], 'extra-class');
    $this->assertContains('original-class', $newElement['content']['_no_layout_builder'][0]['#attributes']['class']);
    $this->assertNotContains('added-class', $newElement['content']['_no_layout_builder'][0]['#attributes']['class']);
    $this->assertNotContains('extra-class', $newElement['content']['_no_layout_builder'][0]['#attributes']['class']);

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
  }

}
