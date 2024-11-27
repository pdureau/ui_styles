<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles_ui_patterns\Kernel\Source;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;
use Drupal\ui_patterns\SourceInterface;
use Drupal\ui_patterns\SourcePluginBase;

/**
 * Test attributes styles source.
 *
 * @coversDefaultClass \Drupal\ui_styles_ui_patterns\Plugin\UiPatterns\Source\AttributesStyles
 *
 * @group ui_styles
 * @group ui_styles_ui_patterns
 */
class AttributesStylesTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ui_patterns',
    'ui_patterns_test',
    'ui_styles',
    'ui_styles_test',
    'ui_styles_ui_patterns',
  ];

  /**
   * The source plugin being tested.
   *
   * @var \Drupal\ui_patterns\SourceInterface
   */
  protected SourceInterface $source;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    /** @var \Drupal\ui_patterns\SourcePluginManager $sourceManager */
    $sourceManager = $this->container->get('plugin.manager.ui_patterns_source');
    /** @var \Drupal\Core\Theme\ComponentPluginManager $componentManager */
    $componentManager = $this->container->get('plugin.manager.sdc');

    $component_id = 'ui_patterns_test:test-component';
    /** @var array $component */
    $component = $componentManager->getDefinition($component_id);
    $propId = 'attributes_ui_patterns';
    $pluginId = 'ui_styles_attributes';
    $configuration = [
      'source' => [
        'styles' => [
          'ui_styles_test' => 'test',
          '_ui_styles_extra' => 'extra',
        ],
      ],
    ];
    $context = [];

    $configuration = SourcePluginBase::buildConfiguration($propId, $component['props']['properties'][$propId], $configuration, $context);
    /** @var \Drupal\ui_patterns\SourceInterface $plugin */
    $plugin = $sourceManager->createInstance($pluginId, $configuration);
    $this->source = $plugin;
  }

  /**
   * Test the plugin method.
   *
   * @covers ::getPropValue
   */
  public function testGetValue(): void {
    /** @var array $definition */
    $definition = $this->source->getPropDefinition();
    $propValue = $this->source->getValue($definition['ui_patterns']['type_definition']);
    $expectedAttributes = [
      'class' => [
        'test',
        'extra',
      ],
    ];
    $this->assertEquals($expectedAttributes, $propValue);
  }

  /**
   * Test the plugin method.
   *
   * @covers ::settingsForm
   */
  public function testSettingsForm(): void {
    $form = [];
    $formState = new FormState();
    $form = $this->source->settingsForm($form, $formState);
    $expectedForm = [
      'styles' => [
        '#type' => 'container',
        '#tree' => TRUE,
        'ui_styles_test' => [
          '#type' => 'select',
          '#title' => 'Test (used)',
          '#options' => [
            'test' => 'Test',
          ],
          '#empty_option' => '- None -',
          '#default_value' => 'test',
          '#weight' => 0,
        ],
        '_ui_styles_extra' => [
          '#type' => 'textfield',
          '#title' => 'Extra classes',
          // phpcs:disable DrupalPractice.General.DescriptionT.DescriptionT
          '#description' => 'You can add many values using spaces as separators.',
          '#default_value' => 'extra',
        ],
      ],
    ];
    $form['styles']['_ui_styles_extra']['#title'] = $form['styles']['_ui_styles_extra']['#title'] instanceof MarkupInterface ? $form['styles']['_ui_styles_extra']['#title']->__toString() : $form['styles']['_ui_styles_extra']['#title'];
    $form['styles']['_ui_styles_extra']['#description'] = $form['styles']['_ui_styles_extra']['#description'] instanceof MarkupInterface ? $form['styles']['_ui_styles_extra']['#description']->__toString() : $form['styles']['_ui_styles_extra']['#description'];
    $this->assertEquals($expectedForm, $form);
  }

}
