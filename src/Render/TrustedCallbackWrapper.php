<?php

declare(strict_types = 1);

namespace Drupal\ui_styles\Render;

use Drupal\Core\Security\DoTrustedCallbackTrait;

/**
 * Ensures that TrustedCallbackInterface can be enforced for callback methods.
 */
class TrustedCallbackWrapper {
  use DoTrustedCallbackTrait;

}
