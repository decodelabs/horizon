<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\PageAction;

use DecodeLabs\Exceptional;
use DecodeLabs\Greenleaf\ActionTrait;
use DecodeLabs\Greenleaf\PageAction;
use DecodeLabs\Greenleaf\PageActionTrait;
use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Greenleaf\Route\Page as PageRoute;
use DecodeLabs\Greenleaf\Route\Parameter;
use DecodeLabs\Greenleaf\Request as LeafRequest;
use DecodeLabs\Horizon\Page;
use DecodeLabs\Tagged\Component\Fragment;
use DecodeLabs\Monarch;
use ReflectionFunction;

class Php implements PageAction
{
    use ActionTrait;
    use PageActionTrait;

    public int $priority = 5;

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

    /**
     * Generator routes
     */
    public function generateRoutes(): iterable
    {
        foreach($this->scanPageFiles('php') as $name => $file) {
            /** @var array<Route> */
            $routes = [];
            $fragment = new Fragment($file->getPath());
            $ref = new ReflectionFunction($fragment->fragment);
            $attributes = $ref->getAttributes();
            /** @var array<Parameter> */
            $parameters = [];

            foreach($attributes as $attribute) {
                if(is_a($attribute->name, Parameter::class, true)) {
                    $parameters[] = $attribute->newInstance();
                    continue;
                }

                if(is_a($attribute->name, Route::class, true)) {
                    $routes[] = $attribute->newInstance();
                    continue;
                }
            }

            if(empty($routes)) {
                $routes[] = new PageRoute(
                    pattern: $this->nameToPattern($name),
                    target: $name.'.php'
                );
            }

            /** @var Route $route */
            foreach($routes as $route) {
                if(
                    $route instanceof PageRoute &&
                    !str_ends_with((string)$route->target, '.php')
                ) {
                    $route->target = $route->target->withPath(fn($path) => $path?->withExtension('php'));
                }

                if(empty($route->methods)) {
                    $route->forMethod('GET');
                }

                /** @var Parameter $parameter */
                foreach($parameters as $parameter) {
                    $route->addParameter($parameter);
                }

                yield $route;
            }
        }
    }
}
