<?php

declare(strict_types = 1);

namespace Drupal\Tests\ui_styles\Unit;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Theme\Registry;
use Drupal\Tests\UnitTestCase;
use Drupal\ui_styles\StylePluginManager;
use Drupal\ui_styles_test\DummyStylePluginManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Test the UI Styles plugin manager.
 *
 * @group ui_styles
 *
 * @coversDefaultClass \Drupal\ui_styles\StylePluginManager
 */
class UiStylesPluginManagerTest extends UnitTestCase {

  /**
   * The Style plugin manager.
   *
   * @var \Drupal\ui_styles\StylePluginManager
   */
  protected $stylePluginManager;

  /**
   * The plugin discovery.
   *
   * @var \Drupal\Component\Plugin\Discovery\DiscoveryInterface
   */
  protected $discovery;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The container.
   *
   * @var \Symfony\Component\DependencyInjection\TaggedContainerInterface
   */
  protected $container;

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
    $this->messenger = $this->createMock(MessengerInterface::class);

    $this->stylePluginManager = new DummyStylePluginManager($moduleHandler, $themeHandler, $this->getStringTranslationStub(), $cache, $this->messenger, $this->styles);
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

    $expected = $definition + [
      'enabled' => TRUE,
      'label' => '',
      'description' => '',
      'options' => [],
    ];
    $this->stylePluginManager->processDefinition($definition, $plugin_id);
    $this->assertSame($definition, $expected);
  }

  /**
   * Test the alterForm().
   *
   * @covers ::alterForm
   */
  public function testAlterForm(): void {
    $form = [
      'actions' => ['#weight' => 1],
    ];
    $extra = 'has_extra';

    $form = $this->stylePluginManager->alterForm($form, [
      'test1' => 'opt2',
      'test2' => 'opt3',
    ], $extra);

    $this->assertSame($form['actions']['#weight'], 100);
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
    $this->assertContains('original-class', $newElement['#no_attributes']['class']);

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
