<?php

declare(strict_types = 1);

namespace Drupal\Tests\ui_styles\Kernel;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\KernelTests\KernelTestBase;
use Drupal\ui_styles_test\MachineNameTraitTestClass;

/**
 * Kernel tests for Machine Name Trait.
 *
 * @group ui_styles
 *
 * @coversDefaultClass \Drupal\ui_styles\MachineNameTrait
 */
class MachineNameTraitTest extends KernelTestBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'ui_styles_test',
  ];

  /**
   * The class used to wrap the trait.
   *
   * @var \Drupal\ui_styles_test\MachineNameTraitTestClass
   */
  protected MachineNameTraitTestClass $testClass;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    /** @var \Drupal\Component\Transliteration\TransliterationInterface $transliteration */
    $transliteration = $this->container->get('transliteration');
    $this->testClass = new MachineNameTraitTestClass($transliteration);
  }

  /**
   * Test getMachineName().
   *
   * @covers ::getMachineName
   *
   * @dataProvider providerTestStrings
   */
  public function testGetMachineNameString(TranslatableMarkup|string $input, string $expected): void {
    $this->assertSame($expected, $this->testClass->callMachineName($input));
  }

  /**
   * Test getMachineName().
   *
   * @covers ::getMachineName
   *
   * @dataProvider providerTestStrings
   */
  public function testGetMachineNameTranslatableMarkup(string $input, string $expected): void {
    // phpcs:disable Drupal.Semantics.FunctionT.NotLiteralString
    $this->assertSame($expected, $this->testClass->callMachineName($this->t($input)));
  }

  /**
   * Data provider for test methods.
   */
  public function providerTestStrings(): array {
    return [
      'string' => [
        'test',
        'test',
      ],
      'space' => [
        'test test',
        'test_test',
      ],
      'special characters' => [
        "test'test@",
        'test_test_',
      ],
    ];
  }

}
