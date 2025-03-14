<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon;

use DecodeLabs\Harvest\Response;
use DecodeLabs\Harvest\ResponseProxy;

class Page implements ResponseProxy {

    public HeadContainer $head;
    public BodyContainer $body;

    public function __construct(
        ?HeadContainer $head = null,
        ?BodyContainer $body = null
    ) {
        $this->head = $head ?? new HeadContainer();
        $this->body = $body ?? new BodyContainer();
    }

    public function toHttpResponse(): Response {
        dd($this);
    }
}
