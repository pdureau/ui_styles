<?php

declare(strict_types=1);

namespace Drupal\ui_styles\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines UI Styles source attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Source extends Plugin {

  /**
   * Constructor.
   *
   * @param string $id
   *   The plugin ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $label
   *   (optional) The human-readable name of the source.
   * @param int|null $weight
   *   (optional) An integer to determine the weight of this source.
   * @param class-string|null $deriver
   *   (optional) The deriver class.
   */
  public function __construct(
    public readonly string $id,
    public readonly ?TranslatableMarkup $label = NULL,
    public readonly ?int $weight = 0,
    public readonly ?string $deriver = NULL,
  ) {}

}
