<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon;

use Closure;
use DecodeLabs\Exceptional;
use DecodeLabs\Monarch;

class FragmentLoader
{
    protected(set) string $path;
    protected(set) ?string $resolvedPath = null;
    protected(set) ?Closure $fragment = null;

    public function __construct(
        string $path
    ) {
        $this->path = $path;

        if(!str_ends_with($path, '.php')) {
            $path .= '.php';
        }

        $this->resolvedPath = Monarch::$paths->resolve($path);
    }

    public function load(): Closure
    {
        if($this->fragment) {
            return $this->fragment;
        }

        if(
            $this->resolvedPath === null ||
            !is_file($this->resolvedPath)
        ) {
            throw Exceptional::InvalidArgument(
                message: 'Fragment file could not be found',
                data: $this->resolvedPath
            );
        }

        $fragment = require $this->resolvedPath;

        if(!is_callable($fragment)) {
            throw Exceptional::Setup(
                'Fragment returned from file must be callable',
                data: $this->resolvedPath
            );
        }

        if($fragment instanceof Closure) {
            $this->fragment = $fragment;
        } else {
            $this->fragment = Closure::fromCallable($fragment);
        }

        return $this->fragment;
    }
}
