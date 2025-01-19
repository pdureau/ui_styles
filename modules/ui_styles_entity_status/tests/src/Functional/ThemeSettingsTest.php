<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles_entity_status\Functional;

use Drupal\Core\Url;
use Drupal\ui_styles_entity_status\UiStylesEntityStatusInterface;

/**
 * Theme settings functional tests.
 *
 * @group ui_styles
 * @group ui_styles_entity_status
 */
class ThemeSettingsTest extends UiStylesEntityStatusFunctionalTestBase {

  /**
   * Test theme form.
   *
   * Test that only modules, parent themes and theme styles appear.
   */
  public function testPluginsDetectionOnThemeSettingsForm(): void {
    $this->drupalLogin($this->adminUser);

    $expected_results = [
      'ui_styles_test_theme3' => [
        'present' => [
          'ui_styles_entity_status_unpublished[wrapper][_ui_styles_extra]',
          'ui_styles_entity_status_unpublished[wrapper][ui_styles_test]',
        ],
        'absent' => [
          'ui_styles_entity_status_unpublished[wrapper][ui_styles_ui_styles_test_theme1]',
          'ui_styles_entity_status_unpublished[wrapper][ui_styles_ui_styles_test_theme2]',
          'ui_styles_entity_status_unpublished[wrapper][ui_styles_ui_styles_test_subtheme]',
        ],
      ],
      'ui_styles_test_theme2' => [
        'present' => [
          'ui_styles_entity_status_unpublished[wrapper][_ui_styles_extra]',
          'ui_styles_entity_status_unpublished[wrapper][ui_styles_test]',
          'ui_styles_entity_status_unpublished[wrapper][ui_styles_ui_styles_test_theme2]',
        ],
        'absent' => [
          'ui_styles_entity_status_unpublished[wrapper][ui_styles_ui_styles_test_theme1]',
          'ui_styles_entity_status_unpublished[wrapper][ui_styles_ui_styles_test_subtheme]',
        ],
      ],
      'ui_styles_test_theme1' => [
        'present' => [
          'ui_styles_entity_status_unpublished[wrapper][_ui_styles_extra]',
          'ui_styles_entity_status_unpublished[wrapper][ui_styles_test]',
          'ui_styles_entity_status_unpublished[wrapper][ui_styles_ui_styles_test_theme1]',
        ],
        'absent' => [
          'ui_styles_entity_status_unpublished[wrapper][ui_styles_ui_styles_test_theme2]',
          'ui_styles_entity_status_unpublished[wrapper][ui_styles_ui_styles_test_subtheme]',
        ],
      ],
      'ui_styles_test_subtheme' => [
        'present' => [
          'ui_styles_entity_status_unpublished[wrapper][_ui_styles_extra]',
          'ui_styles_entity_status_unpublished[wrapper][ui_styles_test]',
          'ui_styles_entity_status_unpublished[wrapper][ui_styles_ui_styles_test_theme1]',
          'ui_styles_entity_status_unpublished[wrapper][ui_styles_ui_styles_test_subtheme]',
        ],
        'absent' => [
          'ui_styles_entity_status_unpublished[wrapper][ui_styles_ui_styles_test_theme2]',
        ],
      ],
      'ui_styles_test_subsubtheme' => [
        'present' => [
          'ui_styles_entity_status_unpublished[wrapper][_ui_styles_extra]',
          'ui_styles_entity_status_unpublished[wrapper][other][ui_styles_test]',
          'ui_styles_entity_status_unpublished[wrapper][other][ui_styles_ui_styles_test_theme1]',
          'ui_styles_entity_status_unpublished[wrapper][other][ui_styles_ui_styles_test_subtheme]',
          'ui_styles_entity_status_unpublished[wrapper][subsubtheme_group][ui_styles_ui_styles_test_subsubtheme]',
        ],
        'absent' => [
          'ui_styles_entity_status_unpublished[wrapper][other][ui_styles_ui_styles_test_theme2]',
        ],
      ],
    ];

    foreach ($expected_results as $theme => $form_infos) {
      $this->drupalGet(Url::fromRoute('system.theme_settings_theme', [
        'theme' => $theme,
      ]));

      foreach ($form_infos['present'] as $form_element_id) {
        $this->assertSession()->elementExists('css', '[name="' . $form_element_id . '"]');
      }

      foreach ($form_infos['absent'] as $form_element_id) {
        $this->assertSession()->elementNotExists('css', '[name="' . $form_element_id . '"]');
      }
    }
  }

  /**
   * Test config state before saving.
   */
  public function testBeforeThemeSettingsSubmit(): void {
    $theme_settings = $this->config($this->defaultTheme . '.settings');
    $regionsStyles = $theme_settings->get(UiStylesEntityStatusInterface::UNPUBLISHED_CLASSES_THEME_SETTING_KEY);
    $this->assertNull($regionsStyles);
  }

  /**
   * Test config state after save.
   */
  public function testThemeSettingsSubmit(): void {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet(Url::fromRoute('system.theme_settings_theme', [
      'theme' => $this->defaultTheme,
    ]));
    $this->submitForm([
      'ui_styles_entity_status_unpublished[wrapper][_ui_styles_extra]' => 'free-value',
      'ui_styles_entity_status_unpublished[wrapper][other][ui_styles_test]' => 'test',
    ], 'Save configuration');

    $theme_settings = $this->config($this->defaultTheme . '.settings');
    $regionsStyles = $theme_settings->get(UiStylesEntityStatusInterface::UNPUBLISHED_CLASSES_THEME_SETTING_KEY);
    $this->assertIsArray($regionsStyles);

    $this->assertEquals([
      'selected' => [
        'test' => 'test',
      ],
      'extra' => 'free-value',
    ], $regionsStyles);
  }

}
