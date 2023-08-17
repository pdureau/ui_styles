<?php

declare(strict_types = 1);

namespace Drupal\Tests\ui_styles_page\Functional;

use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\ui_styles_page\UiStylesPageInterface;

/**
 * Test uninstall ui_styles_page module.
 *
 * @group ui_styles
 * @group ui_styles_page
 */
class UiStylesPageUninstallTest extends UiStylesPageFunctionalTestBase {

  /**
   * The module installer service.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected ModuleInstallerInterface $moduleInstaller;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->moduleInstaller = $this->container->get('module_installer');
  }

  /**
   * Test function.
   */
  public function testUninstall(): void {
    $themeSettings = $this->configFactory->getEditable($this->defaultTheme . '.settings');
    $themeSettings->set(UiStylesPageInterface::REGION_STYLES_KEY_THEME_SETTINGS, [
      'sidebar_first' => [
        'selected' => [
          'fake' => 'fake',
        ],
        'extra' => 'free-value',
      ],
    ]);
    $themeSettings->save();

    $css_variables_settings = $themeSettings->get(UiStylesPageInterface::REGION_STYLES_KEY_THEME_SETTINGS);
    $this->assertNotNull($css_variables_settings);

    $this->moduleInstaller->uninstall(['ui_styles_page']);

    $themeSettings = $this->configFactory->getEditable($this->defaultTheme . '.settings');
    $css_variables_settings = $themeSettings->get(UiStylesPageInterface::REGION_STYLES_KEY_THEME_SETTINGS);
    $this->assertNull($css_variables_settings);
  }

}
