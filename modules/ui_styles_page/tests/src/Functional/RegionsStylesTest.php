<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles_page\Functional;

use Drupal\Core\Url;
use Drupal\ui_styles_page\UiStylesPageInterface;

/**
 * Regions styles functional tests.
 *
 * @group ui_styles
 * @group ui_styles_page
 */
class RegionsStylesTest extends UiStylesPageFunctionalTestBase {

  /**
   * Test theme regions form.
   *
   * Test that only modules, parent themes and theme styles appear.
   */
  public function testPluginsDetectionOnThemeSettingsForm(): void {
    $this->drupalLogin($this->adminUser);

    $expected_results = [
      'ui_styles_test_theme3' => [
        'present' => [
          'ui_styles_regions[sidebar_first][wrapper][_ui_styles_extra]',
          'ui_styles_regions[sidebar_first][wrapper][ui_styles_test]',
        ],
        'absent' => [
          'ui_styles_regions[sidebar_first][wrapper][ui_styles_ui_styles_test_theme1]',
          'ui_styles_regions[sidebar_first][wrapper][ui_styles_ui_styles_test_theme2]',
          'ui_styles_regions[sidebar_first][wrapper][ui_styles_ui_styles_test_subtheme]',
        ],
      ],
      'ui_styles_test_theme2' => [
        'present' => [
          'ui_styles_regions[sidebar_first][wrapper][_ui_styles_extra]',
          'ui_styles_regions[sidebar_first][wrapper][ui_styles_test]',
          'ui_styles_regions[sidebar_first][wrapper][ui_styles_ui_styles_test_theme2]',
        ],
        'absent' => [
          'ui_styles_regions[sidebar_first][wrapper][ui_styles_ui_styles_test_theme1]',
          'ui_styles_regions[sidebar_first][wrapper][ui_styles_ui_styles_test_subtheme]',
        ],
      ],
      'ui_styles_test_theme1' => [
        'present' => [
          'ui_styles_regions[sidebar_first][wrapper][_ui_styles_extra]',
          'ui_styles_regions[sidebar_first][wrapper][ui_styles_test]',
          'ui_styles_regions[sidebar_first][wrapper][ui_styles_ui_styles_test_theme1]',
        ],
        'absent' => [
          'ui_styles_regions[sidebar_first][wrapper][ui_styles_ui_styles_test_theme2]',
          'ui_styles_regions[sidebar_first][wrapper][ui_styles_ui_styles_test_subtheme]',
        ],
      ],
      'ui_styles_test_subtheme' => [
        'present' => [
          'ui_styles_regions[sidebar_first][wrapper][_ui_styles_extra]',
          'ui_styles_regions[sidebar_first][wrapper][ui_styles_test]',
          'ui_styles_regions[sidebar_first][wrapper][ui_styles_ui_styles_test_theme1]',
          'ui_styles_regions[sidebar_first][wrapper][ui_styles_ui_styles_test_subtheme]',
        ],
        'absent' => [
          'ui_styles_regions[sidebar_first][wrapper][ui_styles_ui_styles_test_theme2]',
        ],
      ],
      'ui_styles_test_subsubtheme' => [
        'present' => [
          'ui_styles_regions[sidebar_first][wrapper][_ui_styles_extra]',
          'ui_styles_regions[sidebar_first][wrapper][other][ui_styles_test]',
          'ui_styles_regions[sidebar_first][wrapper][other][ui_styles_ui_styles_test_theme1]',
          'ui_styles_regions[sidebar_first][wrapper][other][ui_styles_ui_styles_test_subtheme]',
          'ui_styles_regions[sidebar_first][wrapper][subsubtheme_group][ui_styles_ui_styles_test_subsubtheme]',
        ],
        'absent' => [
          'ui_styles_regions[sidebar_first][wrapper][other][ui_styles_ui_styles_test_theme2]',
        ],
      ],
    ];

    foreach ($expected_results as $theme => $form_infos) {
      $this->drupalGet(Url::fromRoute('ui_styles_page.regions.theme_settings', [
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
    $regionsStyles = $theme_settings->get(UiStylesPageInterface::REGION_STYLES_KEY_THEME_SETTINGS);
    $this->assertNull($regionsStyles);
  }

  /**
   * Test config state after save.
   */
  public function testThemeSettingsSubmit(): void {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet(Url::fromRoute('ui_styles_page.regions.theme_settings', [
      'theme' => $this->defaultTheme,
    ]));
    $this->submitForm([
      'ui_styles_regions[sidebar_first][wrapper][_ui_styles_extra]' => 'free-value',
      'ui_styles_regions[sidebar_first][wrapper][other][ui_styles_test]' => 'test',
    ], 'Save configuration');

    $theme_settings = $this->config($this->defaultTheme . '.settings');
    $regionsStyles = $theme_settings->get(UiStylesPageInterface::REGION_STYLES_KEY_THEME_SETTINGS);
    $this->assertIsArray($regionsStyles);

    $this->assertEquals([
      'sidebar_first' => [
        'selected' => [
          'test' => 'test',
        ],
        'extra' => 'free-value',
      ],
    ], $regionsStyles);
  }

  /**
   * Test if classes are set correctly.
   */
  public function testHtmlResult(): void {
    $theme_settings = $this->config($this->defaultTheme . '.settings');
    $theme_settings->set(UiStylesPageInterface::REGION_STYLES_KEY_THEME_SETTINGS, [
      'content' => [
        'selected' => [
          'fake' => 'fake',
        ],
        'extra' => 'free-value',
      ],
    ]);

    $theme_settings->save();
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('<front>');
    $this->assertSession()->elementExists('css', '.fake.free-value');
  }

}
