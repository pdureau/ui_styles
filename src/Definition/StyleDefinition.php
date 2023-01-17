<?php

declare(strict_types = 1);

namespace Drupal\ui_styles\Definition;

use Drupal\Component\Plugin\Definition\PluginDefinition;

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
    'category' => '',
    'options' => [],
    'previewed_with' => [],
    'previewed_as' => 'inside',
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
   * Return array definition.
   *
   * @return array
   *   Array definition.
   */
  public function toArray(): array {
    return $this->definition;
  }

}
