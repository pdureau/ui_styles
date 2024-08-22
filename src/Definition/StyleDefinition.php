<?php

declare(strict_types=1);

namespace Drupal\ui_styles\Definition;

use Drupal\Component\Plugin\Definition\PluginDefinition;
use Drupal\Core\Url;

/**
 * Style definition class.
 */
class StyleDefinition extends PluginDefinition {

  /**
   * Style definition.
   *
   * @var array
   */
  protected array $definition = [
    'id' => '',
    'enabled' => TRUE,
    'label' => '',
    'description' => '',
    'links' => [],
    'category' => '',
    'options' => [],
    'empty_option' => '- None -',
    'previewed_with' => [],
    'previewed_as' => 'inside',
    'icon' => '',
    'weight' => 0,
    'additional' => [],
    'provider' => '',
  ];

  /**
   * Constructor.
   */
  public function __construct(array $definition = []) {
    foreach ($definition as $name => $value) {
      if (\array_key_exists($name, $this->definition)) {
        $this->definition[$name] = $value;
      }
      else {
        $this->definition['additional'][$name] = $value;
      }
    }

    $this->id = $this->definition['id'];
  }

  /**
   * Getter.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|string
   *   Property value.
   */
  public function getLabel() {
    return $this->definition['label'];
  }

  /**
   * Setter.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|string $label
   *   Property value.
   *
   * @return $this
   */
  public function setLabel($label) {
    $this->definition['label'] = $label;
    return $this;
  }

  /**
   * Getter.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|string
   *   Property value.
   */
  public function getDescription() {
    return $this->definition['description'];
  }

  /**
   * Setter.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|string $description
   *   Property value.
   *
   * @return $this
   */
  public function setDescription($description) {
    $this->definition['description'] = $description;
    return $this;
  }

  /**
   * Getter.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|string
   *   Property value.
   */
  public function getCategory() {
    return $this->definition['category'];
  }

  /**
   * Setter.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|string $category
   *   Property value.
   *
   * @return $this
   */
  public function setCategory($category) {
    $this->definition['category'] = $category;
    return $this;
  }

  /**
   * If the plugin is in a category.
   *
   * @return bool
   *   TRUE if a category is defined.
   */
  public function hasCategory(): bool {
    return !empty($this->getCategory());
  }

  /**
   * Getter.
   *
   * @return array
   *   Property value.
   */
  public function getOptions(): array {
    return $this->definition['options'];
  }

  /**
   * Get options as options.
   *
   * @return array
   *   Options as select options.
   */
  public function getOptionsAsOptions(): array {
    $options = [];
    foreach ($this->getOptions() as $option_id => $option) {
      if (\is_scalar($option)) {
        $options[$option_id] = $option;
      }
      elseif (isset($option['label'])) {
        $options[$option_id] = $option['label'];
      }
    }
    return $options;
  }

  /**
   * Get options for preview.
   *
   * @return array
   *   Options for preview.
   */
  public function getOptionsForPreview(): array {
    $style_previewed_as = $this->getPreviewedAs();
    $style_previewed_with = $this->getPreviewedWith();

    $options = [];
    foreach ($this->getOptions() as $option_id => $option) {
      $options[$option_id] = [
        'label' => '',
        'description' => '',
        'previewed_with' => $style_previewed_with,
        'previewed_as' => $style_previewed_as,
      ];

      // Label.
      if (\is_scalar($option)) {
        $options[$option_id]['label'] = $option;
      }
      elseif (isset($option['label'])) {
        $options[$option_id]['label'] = $option['label'];
      }

      // Description.
      if (\is_array($option) && isset($option['description'])) {
        $options[$option_id]['description'] = $option['description'];
      }

      // Previewed_with.
      if (\is_array($option) && isset($option['previewed_with'])) {
        $options[$option_id]['previewed_with'] = \array_merge(
          $options[$option_id]['previewed_with'],
          $option['previewed_with']
        );
      }
    }
    return $options;
  }

  /**
   * Get options for preview.
   *
   * @return array
   *   Options for preview.
   */
  public function getOptionsWithIcon(): array {
    $style_icon = $this->getIcon();
    $style_previewed_with = $this->getPreviewedWith();

    $options = [];
    foreach ($this->getOptions() as $option_id => $option) {
      $options[$option_id] = [
        'label' => '',
        'icon' => $style_icon,
        'previewed_with' => $style_previewed_with,
      ];

      // Label.
      if (\is_scalar($option)) {
        $options[$option_id]['label'] = $option;
      }
      elseif (isset($option['label'])) {
        $options[$option_id]['label'] = $option['label'];
      }

      // Icon.
      if (\is_array($option) && isset($option['icon'])) {
        $options[$option_id]['icon'] = $option['icon'];
      }

      // Previewed_with.
      if (\is_array($option) && isset($option['previewed_with'])) {
        $options[$option_id]['previewed_with'] = \array_merge(
          // @phpstan-ignore-next-line
          $options[$option_id]['previewed_with'],
          $option['previewed_with']
        );
      }
      if (empty($options[$option_id]['previewed_with'])) {
        unset($options[$option_id]['previewed_with']);
      }
    }
    return $options;
  }

  /**
   * Setter.
   *
   * @param array $options
   *   Property value.
   *
   * @return $this
   */
  public function setOptions(array $options) {
    $this->definition['options'] = $options;
    return $this;
  }

  /**
   * Getter.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|string
   *   Property value.
   */
  public function getEmptyOption() {
    return $this->definition['empty_option'];
  }

  /**
   * Setter.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|string $emptyOption
   *   Property value.
   *
   * @return $this
   */
  public function setEmptyOption($emptyOption) {
    $this->definition['empty_option'] = $emptyOption;
    return $this;
  }

  /**
   * Getter.
   *
   * @return array
   *   Property value.
   */
  public function getPreviewedWith(): array {
    return $this->definition['previewed_with'];
  }

  /**
   * Setter.
   *
   * @param array $previewedWith
   *   Property value.
   *
   * @return $this
   */
  public function setPreviewedWith(array $previewedWith) {
    $this->definition['previewed_with'] = $previewedWith;
    return $this;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getPreviewedAs(): string {
    return $this->definition['previewed_as'];
  }

  /**
   * Setter.
   *
   * @param string $previewedAs
   *   Property value.
   *
   * @return $this
   */
  public function setPreviewedAs(string $previewedAs) {
    $this->definition['previewed_as'] = $previewedAs;
    return $this;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getIcon() {
    return $this->definition['icon'];
  }

  /**
   * Setter.
   *
   * @param string $icon
   *   Property value.
   *
   * @return $this
   */
  public function setIcon($icon) {
    $this->definition['icon'] = $icon;
    return $this;
  }

  /**
   * Getter.
   *
   * @return bool
   *   Property value.
   */
  public function isEnabled(): bool {
    return $this->definition['enabled'];
  }

  /**
   * Getter.
   *
   * @return int
   *   Property value.
   */
  public function getWeight(): int {
    return $this->definition['weight'];
  }

  /**
   * Setter.
   *
   * @param int $weight
   *   Property value.
   *
   * @return $this
   */
  public function setWeight(int $weight) {
    $this->definition['weight'] = $weight;
    return $this;
  }

  /**
   * Getter.
   *
   * @return array
   *   Property value.
   */
  public function getAdditional(): array {
    return $this->definition['additional'];
  }

  /**
   * Setter.
   *
   * @param array $additional
   *   Property value.
   *
   * @return $this
   */
  public function setAdditional(array $additional) {
    $this->definition['additional'] = $additional;
    return $this;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getProvider(): string {
    return $this->definition['provider'];
  }

  /**
   * Setter.
   *
   * @param string $provider
   *   Property value.
   *
   * @return $this
   */
  public function setProvider(string $provider) {
    $this->definition['provider'] = $provider;
    return $this;
  }

  /**
   * Getter.
   *
   * @return array
   *   The links.
   */
  public function getLinks(): array {
    $links = [];

    foreach ($this->definition['links'] as $link) {
      if (!\is_array($link)) {
        $link = [
          'url' => $link,
        ];
      }

      $link += [
        'title' => 'External documentation',
      ];

      $links[] = $link;
    }

    return $links;
  }

  /**
   * Setter.
   *
   * @param array $links
   *   Property value.
   *
   * @return $this
   */
  public function setLinks(array $links) {
    $this->definition['links'] = $links;
    return $this;
  }

  /**
   * Construct render links.
   *
   * @return array
   *   Render links.
   */
  public function getRenderLinks(): array {
    $renderLinks = [];
    foreach ($this->getLinks() as $link) {
      $renderLinks[] = $this->renderLink($link);
    }
    return $renderLinks;
  }

  /**
   * Return array definition.
   *
   * @return array
   *   Array definition.
   */
  public function toArray(): array {
    $definition = $this->definition;
    $definition['preview_options'] = $this->getOptionsForPreview();
    $definition['render_links'] = $this->getRenderLinks();
    return $definition;
  }

  /**
   * Render link.
   *
   * @param array $link
   *   A link from getLinks method.
   *
   * @return array
   *   The link render element.
   */
  protected function renderLink(array $link): array {
    $renderLink = [
      '#type' => 'link',
      '#title' => $link['title'],
    ];

    if (!empty($link['url'])) {
      $renderLink['#url'] = Url::fromUri($link['url']);
    }

    if (!empty($link['options'])) {
      $renderLink['#options'] = $link['options'];
    }

    if (!empty($link['attributes'])) {
      $renderLink['#attributes'] = $link['attributes'];
    }

    return $renderLink;
  }

}
