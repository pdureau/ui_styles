<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\ui_skins\UiSkinsInterface;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Settings;

/**
 * Test the stylesheet generator service.
 *
 * @group ui_styles
 *
 * @coversDefaultClass \Drupal\ui_styles\Service\StylesheetGenerator
 */
class StylesheetGeneratorTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'ui_skins',
    'ui_styles',
  ];

  /**
   * The test theme.
   *
   * @var string
   */
  protected string $testTheme = 'ui_styles_stylesheet_generator_test';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->container->get('theme_installer')->install([
      $this->testTheme,
    ]);
    $this->activateTheme($this->testTheme);

    // Theme settings rely on System module's system.theme.global configuration.
    $this->installConfig(['system']);
  }

  /**
   * Test generated CSS.
   *
   * Test that a useless CSS variables is removed.
   * Test that a selector not among the styles options is removed.
   * Test prefix.
   *
   * @covers ::generateStylesheet
   */
  public function testGenerateStylesheet(): void {
    /** @var \Drupal\ui_styles\Service\StylesheetGeneratorInterface $styleSheetGenerator */
    $styleSheetGenerator = $this->container->get('ui_styles.stylesheet_generator');

    $generated = $styleSheetGenerator->generateStylesheet('.prefix');
    $expectedCss = $this->getExpectedCss('expected.css');
    $this->assertEquals($expectedCss, $generated);
  }

  /**
   * Test UI Skins integration.
   *
   * @covers ::generateStylesheet
   */
  public function testWithUiSkins(): void {
    /** @var \Drupal\ui_styles\Service\StylesheetGeneratorInterface $styleSheetGenerator */
    $styleSheetGenerator = $this->container->get('ui_styles.stylesheet_generator');
    $theme_settings = $this->config($this->testTheme . '.settings');
    $theme_settings->set(UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY, [
      'foo' => [
        '%my-scope' => 'ui_skins',
      ],
      'bar' => [
        '%my-subsubtheme-class' => 'unused',
      ],
    ]);
    $theme_settings->save();

    $generated = $styleSheetGenerator->generateStylesheet('.prefix');
    $expectedCss = $this->getExpectedCss('expected_with_ui_skins.css');
    $this->assertEquals($expectedCss, $generated);
  }

  /**
   * Load CSS file.
   *
   * @param string $filename
   *   The filename to load.
   *
   * @return string
   *   The CSS
   */
  protected function getExpectedCss(string $filename): string {
    /** @var \Drupal\Core\Extension\ThemeExtensionList $extensionListTheme */
    $extensionListTheme = $this->container->get('extension.list.theme');

    $expectedCssPath = DRUPAL_ROOT . '/' . $extensionListTheme->getPath($this->testTheme) . '/assets/css/' . $filename;
    $expectedCss = \file_get_contents($expectedCssPath);
    $this->assertNotEmpty($expectedCss);
    return $this->getCompactedVersion($expectedCss);
  }

  /**
   * Activates a specified theme.
   *
   * Installs the theme if not already installed and makes it the active theme.
   *
   * @param string $theme_name
   *   The name of the theme to be activated.
   */
  protected function activateTheme(string $theme_name): void {
    $this->container->get('theme_installer')->install([$theme_name]);

    /** @var \Drupal\Core\Theme\ThemeInitializationInterface $theme_initializer */
    $theme_initializer = $this->container->get('theme.initialization');

    /** @var \Drupal\Core\Theme\ThemeManagerInterface $theme_manager */
    $theme_manager = $this->container->get('theme.manager');

    $theme_manager->setActiveTheme($theme_initializer->getActiveThemeByName($theme_name));
    $this->assertSame($theme_name, $theme_manager->getActiveTheme()->getName());
  }

  /**
   * Compact CSS.
   *
   * @param string $css
   *   The CSS to compact.
   *
   * @return string
   *   The compacted CSS.
   */
  protected function getCompactedVersion(string $css): string {
    $parsedCss = (new Parser(
      $css,
      Settings::create()->withLenientParsing(),
    ))->parse();
    return $parsedCss->render(OutputFormat::createCompact());
  }

}
