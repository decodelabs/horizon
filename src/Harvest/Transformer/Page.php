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
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @implements Transformer<HorizonPage>
 */
class Page implements Transformer
{
    public function transform(
        Request $request,
        mixed $page
    ): Response {
        // TODO: add headers
        return Harvest::html(
            $page->render(
                Monarch::isDevelopment()
            )
        );
    }
}
