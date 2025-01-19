<?php

declare(strict_types=1);

namespace Drupal\ui_styles;

/**
 * Contains helper methods for UI Styles.
 */
class UiStylesUtility {

  /**
   * Get selected styles from form values.
   *
   * Handle groups.
   *
   * @param array $formValues
   *   The form values to extract the selected classes from.
   *
   * @return array
   *   The selected values.
   *
   * @deprecated in ui_styles:8.x-1.14 and is removed from ui_styles:2.0.0. Use
   *   the ui_styles_styles form element instead.
   * @see https://www.drupal.org/node/3500750
   *
   * @SuppressWarnings(PHPMD.ErrorControlOperator)
   */
  public static function extractSelectedStyles(array $formValues): array {
    @\trigger_error('UiStylesUtility::extractSelectedStyles() is deprecated in ui_styles:8.x-1.14 and is removed in ui_styles:2.0.0. See https://www.drupal.org/node/3500750', \E_USER_DEPRECATED);
    $selected = [];
    foreach ($formValues as $id => $value) {
      // Case of a group.
      if (\is_array($value)) {
        $selected = \array_merge($selected, self::extractSelectedStyles($value));
      }

      if (empty($value)) {
        continue;
      }

      /** @var string $id */
      if (\strpos($id, 'ui_styles_') === 0) {
        $id = \str_replace('ui_styles_', '', $id);
        $selected[$id] = $value;
      }
    }
    return $selected;
  }

}
