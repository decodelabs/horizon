<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Harvest\Transformer\DecodeLabs\Horizon;

use DecodeLabs\Harvest;
use DecodeLabs\Harvest\Transformer;
use DecodeLabs\Horizon\Page as HorizonPage;
use DecodeLabs\Monarch;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;

/**
 * @implements Transformer<HorizonPage>
 */
class Page implements Transformer
{
    public function transform(
        PsrRequest $request,
        mixed $page
    ): PsrResponse {
        // TODO: add headers
        return Harvest::html(
            $page->render(
                Monarch::isDevelopment()
            )
        );
    }
}
