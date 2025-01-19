<?php

declare(strict_types=1);

// phpcs:disable DrupalPractice.General.DescriptionT.DescriptionT

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
   * Test the plugin method.
   *
   * @covers ::getPropValue
   */
  public function testGetValue(): void {
    $source = $this->getSourceFromConfiguration([
      'styles' => [
        'selected' => [
          'test' => 'test',
        ],
        'extra' => 'extra extra2',
      ],
      'extra' => 'id="my-id" data-foo="bar"',
    ]);

    /** @var array $definition */
    $definition = $source->getPropDefinition();
    $propValue = $source->getValue($definition['ui_patterns']['type_definition']);
    $expectedAttributes = [
      'class' => [
        'test',
        'extra',
        'extra2',
      ],
      'id' => 'my-id',
      'data-foo' => 'bar',
    ];
    $this->assertEquals($expectedAttributes, $propValue);
  }

  /**
   * Test the plugin method.
   *
   * @covers ::settingsForm
   */
  public function testSettingsForm(): void {
    // Test empty config.
    $form = [];
    $formState = new FormState();
    $source = $this->getSourceFromConfiguration();
    $form = $source->settingsForm($form, $formState);
    $expectedForm = [
      'styles' => [
        '#type' => 'ui_styles_styles',
        '#default_value' => [
          'selected' => [],
          'extra' => '',
        ],
        '#wrapper_type' => 'container',
        '#tree' => TRUE,
      ],
      'extra' => [
        '#type' => 'textfield',
        '#title' => 'Extra HTML attributes',
        '#description' => 'HTML attributes with double-quoted values.',
        '#default_value' => '',
        '#placeholder' => 'title="Lorem ipsum" id="my-id"',
      ],
    ];
    $form['extra']['#title'] = $form['extra']['#title'] instanceof MarkupInterface ? $form['extra']['#title']->__toString() : $form['extra']['#title'];
    $form['extra']['#description'] = $form['extra']['#description'] instanceof MarkupInterface ? $form['extra']['#description']->__toString() : $form['extra']['#description'];
    $this->assertEquals($expectedForm, $form);

    // Deprecated config.
    $form = [];
    $formState = new FormState();
    $source = $this->getSourceFromConfiguration([
      'styles' => [
        'ui_styles_test' => 'test',
        '_ui_styles_extra' => 'extra',
      ],
    ]);
    $form = $source->settingsForm($form, $formState);
    $expectedForm = [
      'styles' => [
        '#type' => 'ui_styles_styles',
        '#default_value' => [
          'selected' => [
            'test' => 'test',
          ],
          'extra' => 'extra',
        ],
        '#wrapper_type' => 'container',
        '#tree' => TRUE,
      ],
      'extra' => [
        '#type' => 'textfield',
        '#title' => 'Extra HTML attributes',
        '#description' => 'HTML attributes with double-quoted values.',
        '#default_value' => '',
        '#placeholder' => 'title="Lorem ipsum" id="my-id"',
      ],
    ];
    $form['extra']['#title'] = $form['extra']['#title'] instanceof MarkupInterface ? $form['extra']['#title']->__toString() : $form['extra']['#title'];
    $form['extra']['#description'] = $form['extra']['#description'] instanceof MarkupInterface ? $form['extra']['#description']->__toString() : $form['extra']['#description'];
    $this->assertEquals($expectedForm, $form);

    // With extra attributes.
    $form = [];
    $formState = new FormState();
    $source = $this->getSourceFromConfiguration([
      'styles' => [
        'selected' => [
          'test' => 'test',
        ],
        'extra' => 'extra extra2',
      ],
      'extra' => 'id="my-id" data-foo="bar"',
    ]);
    $form = $source->settingsForm($form, $formState);
    $expectedForm = [
      'styles' => [
        '#type' => 'ui_styles_styles',
        '#default_value' => [
          'selected' => [
            'test' => 'test',
          ],
          'extra' => 'extra extra2',
        ],
        '#wrapper_type' => 'container',
        '#tree' => TRUE,
      ],
      'extra' => [
        '#type' => 'textfield',
        '#title' => 'Extra HTML attributes',
        '#description' => 'HTML attributes with double-quoted values.',
        '#default_value' => 'id="my-id" data-foo="bar"',
        '#placeholder' => 'title="Lorem ipsum" id="my-id"',
      ],
    ];
    $form['extra']['#title'] = $form['extra']['#title'] instanceof MarkupInterface ? $form['extra']['#title']->__toString() : $form['extra']['#title'];
    $form['extra']['#description'] = $form['extra']['#description'] instanceof MarkupInterface ? $form['extra']['#description']->__toString() : $form['extra']['#description'];
    $this->assertEquals($expectedForm, $form);

    // From default value.
    $form = [];
    $formState = new FormState();
    $source = $this->getSourceFromConfiguration([
      'value' => 'class="my-class my-class2" id="my-id" data-foo="bar"',
    ]);
    $form = $source->settingsForm($form, $formState);
    $expectedForm = [
      'styles' => [
        '#type' => 'ui_styles_styles',
        '#default_value' => [
          'selected' => [],
          'extra' => 'my-class my-class2',
        ],
        '#wrapper_type' => 'container',
        '#tree' => TRUE,
      ],
      'extra' => [
        '#type' => 'textfield',
        '#title' => 'Extra HTML attributes',
        '#description' => 'HTML attributes with double-quoted values.',
        '#default_value' => 'id="my-id" data-foo="bar"',
        '#placeholder' => 'title="Lorem ipsum" id="my-id"',
      ],
    ];
    $form['extra']['#title'] = $form['extra']['#title'] instanceof MarkupInterface ? $form['extra']['#title']->__toString() : $form['extra']['#title'];
    $form['extra']['#description'] = $form['extra']['#description'] instanceof MarkupInterface ? $form['extra']['#description']->__toString() : $form['extra']['#description'];
    $this->assertEquals($expectedForm, $form);
  }

  /**
   * Get a source plugin.
   *
   * @param array $configuration
   *   The source configuration.
   *
   * @return \Drupal\ui_patterns\SourceInterface
   *   The source plugin instance.
   */
  protected function getSourceFromConfiguration(array $configuration = []): SourceInterface {
    /** @var \Drupal\ui_patterns\SourcePluginManager $sourceManager */
    $sourceManager = $this->container->get('plugin.manager.ui_patterns_source');
    /** @var \Drupal\Core\Theme\ComponentPluginManager $componentManager */
    $componentManager = $this->container->get('plugin.manager.sdc');

    $component_id = 'ui_patterns_test:test-component';
    /** @var array $component */
    $component = $componentManager->getDefinition($component_id);
    $propId = 'attributes_ui_patterns';
    $pluginId = 'ui_styles_attributes';
    $sourceConfiguration = [
      'source' => $configuration,
    ];
    $context = [];

    $sourceConfiguration = SourcePluginBase::buildConfiguration($propId, $component['props']['properties'][$propId], $sourceConfiguration, $context);
    // @phpstan-ignore-next-line
    return $sourceManager->createInstance($pluginId, $sourceConfiguration);
  }

}
