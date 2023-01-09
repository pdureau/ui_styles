<?php

declare(strict_types = 1);

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
   */
  public static function extractSelectedStyles(array $formValues): array {
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
