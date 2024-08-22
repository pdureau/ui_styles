<?php

declare(strict_types=1);

namespace Drupal\ui_styles\Plugin\UiStyles\Source;

use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ui_styles\Attribute\Source;
use Drupal\ui_styles\Definition\StyleDefinition;
use Drupal\ui_styles\Source\SourcePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Source Toolbar widget.
 *
 * Used if every option has an icon or if there is a global icon in the style.
 */
#[Source(
  id: 'toolbar',
  label: new TranslatableMarkup('Toolbar'),
  weight: 1,
)]
class Toolbar extends SourcePluginBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected RendererInterface $renderer;

  /**
   * The extension path resolver.
   *
   * @var \Drupal\Core\Extension\ExtensionPathResolver
   */
  protected ExtensionPathResolver $extensionPathResolver;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->renderer = $container->get('renderer');
    $instance->extensionPathResolver = $container->get('extension.path.resolver');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function isApplicable(StyleDefinition $definition): bool {
    $options = $definition->getOptionsWithIcon();
    foreach ($options as $option) {
      if (empty($option['icon'])) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetForm(StyleDefinition $definition, string $selected = ''): array {
    return [
      '#type' => 'radios',
      '#title' => $definition->getLabel(),
      '#options' => $this->renderOptions($definition),
      '#default_value' => $selected,
      '#weight' => $definition->getWeight(),
      '#attributes' => [
        'class' => [
          'ui-styles-source-toolbar-plugin',
        ],
      ],
      '#attached' => [
        'library' => [
          'ui_styles/source_plugin_toolbar',
        ],
      ],
    ];
  }

  /**
   * Return array of options for form element.
   *
   * @param \Drupal\ui_styles\Definition\StyleDefinition $definition
   *   The style plugin definition.
   *
   * @return array
   *   The array of options.
   */
  protected function renderOptions(StyleDefinition $definition): array {
    $emptyIcon = [
      '#theme' => 'image',
      '#uri' => $this->extensionPathResolver->getPath('module', 'ui_styles') . '/assets/images/na-icon.png',
      '#title' => $definition->getEmptyOption(),
      '#attributes' => ['loading' => 'lazy'],
    ];
    $options = [
      '' => $this->renderer->renderInIsolation($emptyIcon),
    ];

    foreach ($definition->getOptionsWithIcon() as $option_id => $option) {
      $optionIcon = [
        '#theme' => 'image',
        '#uri' => $option['icon'],
        '#title' => $option['label'],
        '#attributes' => ['loading' => 'lazy'],
      ];
      $options[$option_id] = $this->renderer->renderInIsolation($optionIcon);
    }

    return $options;
  }

}
