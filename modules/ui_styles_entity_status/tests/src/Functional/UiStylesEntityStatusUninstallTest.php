<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles_entity_status\Functional;

use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\ui_styles_entity_status\UiStylesEntityStatusInterface;

/**
 * Test uninstall ui_styles_entity_status module.
 *
 * @group ui_styles
 * @group ui_styles_entity_status
 */
class UiStylesEntityStatusUninstallTest extends UiStylesEntityStatusFunctionalTestBase {

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
   * Test hook_uninstall.
   */
  public function testUninstall(): void {
    $themeSettings = $this->configFactory->getEditable($this->defaultTheme . '.settings');
    $themeSettings->set(UiStylesEntityStatusInterface::UNPUBLISHED_CLASSES_THEME_SETTING_KEY, [
      'selected' => [
        'fake' => 'fake',
      ],
      'extra' => 'free-value',
    ]);
    $themeSettings->save();

    $settings = $themeSettings->get(UiStylesEntityStatusInterface::UNPUBLISHED_CLASSES_THEME_SETTING_KEY);
    $this->assertNotNull($settings);

    $this->moduleInstaller->uninstall(['ui_styles_entity_status']);

    $themeSettings = $this->configFactory->getEditable($this->defaultTheme . '.settings');
    $settings = $themeSettings->get(UiStylesEntityStatusInterface::UNPUBLISHED_CLASSES_THEME_SETTING_KEY);
    $this->assertNull($settings);
  }

}
