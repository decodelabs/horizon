<?php

/**
 * Horizon
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon;

interface Decorator
{
    public function decorate(
        Page $page
    ): void;
}
