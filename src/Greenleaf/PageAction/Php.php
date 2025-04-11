<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\PageAction;

use DecodeLabs\Exceptional;
use DecodeLabs\Greenleaf\PageAction;
use DecodeLabs\Greenleaf\ActionTrait;
use DecodeLabs\Greenleaf\Request as LeafRequest;
use DecodeLabs\Horizon\Page;
use DecodeLabs\Tagged\Component\Fragment;
use DecodeLabs\Monarch;
use Exception;

class Php implements PageAction
{
    use ActionTrait;

    public int $priority = 1;

    /**
     * Handle HTTP request
     */
    public function execute(
        LeafRequest $request
    ): mixed {
        $path = '@pages/'.ltrim($request->leafUrl->getPath(), '/');
        $resolvedPath = Monarch::$paths->resolve($path);

        if(!file_exists($resolvedPath)) {
            throw Exceptional::NotFound(
                message: 'Page not found: '.$path,
                http: 404
            );
        }

        $fragment = new Fragment($resolvedPath);
        $fragment->slingshot = $this->prepareSlingshot($request);

        return new Page($fragment);
    }
}
