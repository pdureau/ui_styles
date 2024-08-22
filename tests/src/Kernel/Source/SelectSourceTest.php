<?php

declare(strict_types=1);

// phpcs:disable DrupalPractice.General.OptionsT.TforValue

namespace Drupal\Tests\ui_styles\Kernel\Source;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ui_styles\Definition\StyleDefinition;

/**
 * @coversDefaultClass \Drupal\ui_styles\Plugin\UiStyles\Source\Select
 *
 * @group ui_styles
 */
class SelectSourceTest extends SourceTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected string $pluginId = 'select';

  /**
   * Tests getWidgetForm method.
   *
   * @covers ::getWidgetForm
   */
  public function testGetWidgetForm(): void {
    /** @var \Drupal\ui_styles\Source\SourceInterface $source */
    $source = $this->sourcePluginManager->createInstance($this->pluginId);
    $styleDefinition = new StyleDefinition([
      'id' => 'test',
      'label' => 'Test label',
      'options' => [
        'simple' => 'Simple',
        'icon' => [
          'label' => 'Icon',
          'icon' => 'icon',
        ],
      ],
      'previewed_as' => 'hidden',
      'empty_option' => 'My empty option',
      'weight' => 5,
    ]);

    $expected = [
      '#type' => 'select',
      '#title' => 'Test label',
      '#options' => [
        'simple' => 'Simple',
        'icon' => 'Icon',
      ],
      '#default_value' => '',
      '#empty_option' => $this->t('My empty option'),
      '#weight' => 5,
    ];
    $this->assertEquals($expected, $source->getWidgetForm($styleDefinition));

    // With a default value.
    $expected['#default_value'] = 'simple';
    $this->assertEquals($expected, $source->getWidgetForm($styleDefinition, 'simple'));

    // With default preview.
    $styleDefinition->setPreviewedAs('');

    $expected = [
      '#type' => 'radios',
      '#title' => 'Test label',
      '#options' => [
        '' => $this->t('My empty option'),
        'simple' => '<span class="ui-styles-source-select-plugin-option simple">Αα</span>Simple',
        'icon' => Markup::create("<span class=\"ui-styles-source-select-plugin-option\"><img loading=\"lazy\" src=\"/icon\" alt=\"\" title=\"Icon\" />\n</span>Icon"),
      ],
      '#default_value' => '',
      '#weight' => 5,
      '#attributes' => [
        'class' => [
          'ui-styles-source-select-plugin',
        ],
      ],
      '#attached' => [
        'library' => [
          'ui_styles/source_plugin_select',
        ],
      ],
    ];
    // Convert to avoid objects comparison.
    foreach ($expected['#options'] as &$option) {
      $option = $option instanceof MarkupInterface ? $option->__toString() : $option;
    }

    $form = $source->getWidgetForm($styleDefinition);
    foreach ($form['#options'] as &$option) {
      $option = $option instanceof MarkupInterface ? $option->__toString() : $option;
    }
    $this->assertEquals($expected, $form);

    // With a default value.
    $expected['#default_value'] = 'simple';
    $form = $source->getWidgetForm($styleDefinition, 'simple');
    foreach ($form['#options'] as &$option) {
      $option = $option instanceof MarkupInterface ? $option->__toString() : $option;
    }
    $this->assertEquals($expected, $form);
  }

}
