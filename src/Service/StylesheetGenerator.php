<?php

declare(strict_types=1);

namespace Drupal\ui_styles\Service;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Asset\LibraryDiscoveryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\ui_skins\UiSkinsInterface;
use Drupal\ui_skins\UiSkinsUtility;
use Drupal\ui_styles\StylePluginManagerInterface;
use Psr\Log\LoggerInterface;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\Settings;

/**
 * Default stylesheet generator service.
 */
class StylesheetGenerator implements StylesheetGeneratorInterface {

  public const LIBRARY_PARSING_LIMIT = 2;

  /**
   * The output format.
   *
   * @var \Sabberworm\CSS\OutputFormat
   */
  protected OutputFormat $outputFormat;

  /**
   * Constructor.
   */
  public function __construct(
    protected ModuleHandlerInterface $moduleHandler,
    protected ThemeHandlerInterface $themeHandler,
    protected LibraryDiscoveryInterface $libraryDiscovery,
    protected LoggerInterface $logger,
    protected StylePluginManagerInterface $stylesManager,
  ) {
    $this->outputFormat = OutputFormat::createCompact();
  }

  /**
   * {@inheritdoc}
   */
  public function generateStylesheet(string $prefix = ''): string {
    [$cssFiles, $styleOptionsClasses] = $this->getCssFilesAndOptions();

    $generatedCss = $this->generateCss($cssFiles, $styleOptionsClasses, $prefix);
    $generatedCssVariables = $this->generateCssVariables($cssFiles, $generatedCss);
    return $generatedCssVariables . $generatedCss;
  }

  /**
   * Return the CSS files content and the styles options for all the themes.
   *
   * @return array
   *   return the CSS files and the styles options for all the themes.
   */
  protected function getCssFilesAndOptions(): array {
    $cssFiles = [];
    $styleOptionsClasses = [];
    foreach ($this->themeHandler->listInfo() as $theme => $themeObject) {
      $themeStyleOptions = $this->getThemeStyleOptions($theme);
      if (empty($themeStyleOptions)) {
        continue;
      }

      $libraries = $this->getThemeLibraries($theme);
      $cssFiles = \array_merge($cssFiles, $this->getCssFilesFromLibraries($libraries));
      $styleOptionsClasses = \array_merge($styleOptionsClasses, $themeStyleOptions);
    }
    return [$cssFiles, $styleOptionsClasses];
  }

  /**
   * Generate CSS file for styles.
   *
   * @param array $cssFiles
   *   The CSS files to parse.
   * @param array $styleOptionsClasses
   *   The style option classes.
   * @param string $prefix
   *   The CSS selector prefix.
   *
   * @return string
   *   The generated CSS with only the styles for style options.
   */
  protected function generateCss(array $cssFiles, array $styleOptionsClasses, string $prefix = ''): string {
    $generatedCss = '';
    foreach ($cssFiles as $cssFileContent) {
      $parsedCss = (new Parser(
        $cssFileContent,
        Settings::create()->withLenientParsing(),
      ))->parse();

      foreach ($parsedCss->getAllDeclarationBlocks() as $block) {
        foreach ($block->getSelectors() as $selector) {
          if (!($selector instanceof Selector)) {
            continue;
          }

          if (!\in_array($selector->getSelector(), $styleOptionsClasses, TRUE)) {
            $block->removeSelector($selector);
            continue;
          }

          if (!empty($prefix)) {
            $selector->setSelector($prefix . ' ' . $selector->getSelector());
          }
        }

        if (!empty($block->getSelectors())) {
          $generatedCss .= $block->render($this->outputFormat);
        }
      }
    }
    return $generatedCss;
  }

  /**
   * Get the CSS providing CSS variables used by style options.
   *
   * Parse generated CSS file to extract the CSS variable used.
   * Parse again CSS files to get the CSS variables.
   *
   * @param array $cssFiles
   *   The CSS files to parse.
   * @param string $generatedCss
   *   The generated CSS from style options.
   *
   * @return string
   *   The generated CSS.
   */
  protected function generateCssVariables(array $cssFiles, string $generatedCss): string {
    $generatedCssVariables = '';

    \preg_match_all('/var\((--.*)(,.*)?\)/U', $generatedCss, $results);
    $cssVariables = $results[1];
    $cssVariables = \array_unique($cssVariables);
    if (empty($cssVariables)) {
      return '';
    }

    foreach ($cssFiles as $cssFileContent) {
      $parsedCss = (new Parser(
        $cssFileContent,
        Settings::create()->withLenientParsing(),
      ))->parse();

      foreach ($parsedCss->getAllDeclarationBlocks() as $block) {
        foreach ($block->getRules() as $rule) {
          $property = $rule->getRule();
          if (!\in_array($property, $cssVariables, TRUE)) {
            $block->removeRule($rule);
          }
        }
        if (!empty($block->getRules())) {
          $generatedCssVariables .= $block->render($this->outputFormat);
        }
      }
    }

    // UI Skins integration.
    if ($this->moduleHandler->moduleExists('ui_skins')) {
      $css_variables = [];
      foreach ($this->themeHandler->listInfo() as $theme => $themeObject) {
        $ui_skins_css_variables_settings = \theme_get_setting(UiSkinsInterface::CSS_VARIABLES_THEME_SETTING_KEY, $theme);
        if (!\is_array($ui_skins_css_variables_settings)) {
          continue;
        }

        // Prepare list of variables grouped by scope.
        foreach ($ui_skins_css_variables_settings as $plugin_id => $scoped_values) {
          $variable_name = UiSkinsUtility::getCssVariableName($plugin_id);
          if (!\in_array($variable_name, $cssVariables, TRUE)) {
            continue;
          }

          foreach ($scoped_values as $scope => $value) {
            $css_variables = NestedArray::mergeDeep($css_variables, [
              UiSkinsUtility::getCssScopeName($scope) => [
                $variable_name => $value,
              ],
            ]);
          }
        }
      }

      if (!empty($css_variables)) {
        $generatedCssVariables .= UiSkinsUtility::getCssVariablesInlineCss($css_variables);
      }
    }

    return $generatedCssVariables;
  }

  /**
   * Retrieve all styles plugins options of a given theme.
   *
   * @param string $theme
   *   The theme machine name.
   *
   * @return array
   *   An array of style options defined for the theme.
   */
  protected function getThemeStyleOptions(string $theme): array {
    $groupedDefinitions = $this->stylesManager->getDefinitionsForTheme($theme);
    $styleOptions = [];

    foreach ($groupedDefinitions as $groupDefinitions) {
      foreach ($groupDefinitions as $definition) {
        foreach ($definition->getOptionsAsOptions() as $option => $label) {
          $styleOptions[] = '.' . $option;
        }
      }
    }

    return $styleOptions;
  }

  /**
   * Retrieve all libraries of a given theme.
   *
   * @param string $theme
   *   The theme machine name.
   *
   * @return array
   *   An array of libraries defined for the theme.
   */
  protected function getThemeLibraries(string $theme): array {
    $themeExtension = $this->themeHandler->getTheme($theme);
    return $themeExtension->info['libraries'] ?? [];
  }

  /**
   * Retrieve all CSS file content used for libraries of a given theme.
   *
   * @param array $libraries
   *   An array of libraries.
   *
   * @return array
   *   An array of CSS file content used.
   *
   * @SuppressWarnings(PHPMD.ErrorControlOperator)
   */
  protected function getCssFilesFromLibraries(array $libraries): array {
    $cssFiles = [];
    foreach ($libraries as $library) {
      [$extension, $name] = \explode('/', $library, static::LIBRARY_PARSING_LIMIT);
      $definition = $this->libraryDiscovery->getLibraryByName($extension, $name);

      if (!$definition || !isset($definition['css'])) {
        continue;
      }

      foreach ($definition['css'] as $cssLevelFiles) {
        if ($cssLevelFiles['type'] == 'external') {
          $cssFilePath = $cssLevelFiles['data'];
        }
        else {
          $cssFilePath = DRUPAL_ROOT . '/' . $cssLevelFiles['data'];
        }

        $file_content = @\file_get_contents($cssFilePath);
        if (!$file_content) {
          $this->logger->error('File @file_path does not exist.', ['@file_path' => $cssFilePath]);
          continue;
        }
        $cssFiles[] = $file_content;
      }
    }
    return $cssFiles;
  }

}
