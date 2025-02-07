<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles\Unit\PageCache;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\ui_styles\PageCache\AllowGeneratedStylesheet;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\ui_styles\PageCache\AllowGeneratedStylesheet
 *
 * @group ui_styles
 */
class AllowGeneratedStylesheetTest extends UnitTestCase {

  /**
   * The policy under test.
   *
   * @var \Drupal\ui_styles\PageCache\AllowGeneratedStylesheet
   */
  protected AllowGeneratedStylesheet $policy;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->policy = new AllowGeneratedStylesheet();
  }

  /**
   * Asserts that caching is allowed if on generated stylesheet.
   *
   * @dataProvider providerTestAllowGeneratedStylesheet
   *
   * @covers ::check
   */
  public function testAllowGeneratedStylesheet(?string $expected_result, string $path): void {
    $request = Request::create($path);
    $result = $this->policy->check($request);
    $this->assertSame($expected_result, $result);
  }

  /**
   * Provides data and expected results for the test method.
   *
   * @return array
   *   Data and expected results.
   */
  public static function providerTestAllowGeneratedStylesheet() {
    return [
      [NULL, '/'],
      [NULL, '/other-path?q=/other/subtrees/'],
      [NULL, '/ui_styles/stylesheet/b'],
      [NULL, '/a/ui_styles/stylesheet'],
      [RequestPolicyInterface::ALLOW, '/ui_styles/stylesheet'],
    ];
  }

}
