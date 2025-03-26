<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon;

use DecodeLabs\Coercion;

trait RenderableTrait
{
    public function __toString(): string
    {
        return Coercion::toString($this->render(true));
    }
}
