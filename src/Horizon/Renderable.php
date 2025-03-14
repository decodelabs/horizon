<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon;

use DecodeLabs\Tagged\Markup;
use Stringable;

interface Renderable extends Stringable
{
    public function render(): Markup;
}
