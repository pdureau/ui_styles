<?php

declare(strict_types=1);

namespace Drupal\ui_styles_entity_status;

/**
 * Provides an interface for ui_styles_entity_status constants.
 */
interface UiStylesEntityStatusInterface {

  /**
   * The theme config key for classes added when an entity is unpublished.
   */
  public const UNPUBLISHED_CLASSES_THEME_SETTING_KEY = 'ui_styles_entity_status_unpublished';

}
