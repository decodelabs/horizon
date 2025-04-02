<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Component;

use Attribute;
use Closure;
use DecodeLabs\Exceptional;
use DecodeLabs\Horizon\FragmentLoader;
use DecodeLabs\Slingshot;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Component;
use DecodeLabs\Tagged\ContentCollection;
use DecodeLabs\Tagged\RenderableTrait;
use DecodeLabs\Tagged\Tag;
use ReflectionAttribute;
use ReflectionFunction;

class Fragment extends Tag implements Component
{
    use RenderableTrait;

    public Closure $fragment;

    /**
     * @var array<string,mixed>
     */
    public array $parameters = [];

    private bool $loaded = false;

    /**
     * Generate fragment
     */
    public function __construct(
        string|callable $fragment,
        mixed ...$parameters
    ) {
        parent::__construct(null);

        if(is_string($fragment)) {
            $loader = new FragmentLoader($fragment);
            $this->fragment = $loader->load();
            $this->loaded = true;
        } else {
            $this->fragment = Closure::fromCallable($fragment);
        }

        /** @var array<string,mixed> $parameters */
        $this->importParameters($parameters);
    }

    public function bind(
        object $object
    ): void {
        if($this->loaded) {
            $this->fragment = Closure::bind(
                // @phpstan-ignore-next-line
                $this->fragment,
                $object
            );
        }
    }

    public function render(
        bool $pretty = false
    ): ?Buffer {
        return $this->__invoke(
            pretty: $pretty
        );
    }

    public function __invoke(
        mixed ...$parameters
    ): ?Buffer {
        $slingshot = new Slingshot(
            parameters: $this->parameters,
        );

        /** @var array<string,mixed> $parameters */
        $output = $slingshot->invoke(
            $this->fragment,
            $parameters
        );

        $pretty =
            ($parameters['pretty'] ?? false) === true ||
            ($this->parameters['pretty'] ?? false) === true;

        return ContentCollection::normalize($output, $pretty);
    }

    /**
     * @param array<int|string,mixed> $parameters
     */
    protected function importParameters(
        array $parameters
    ): void {
        foreach($parameters as $key => $value) {
            if(!is_string($key)) {
                throw Exceptional::InvalidArgument(
                    'Fragment parameters must be passed as key/value pairs'
                );
            }

            $this->parameters[$key] = $value;
        }
    }

    /**
     * @return array<ReflectionAttribute<Attribute>>
     */
    public function getReflectionAttributes(
        ?string $name = null,
        int $flags = 0
    ): array {
        $ref = new ReflectionFunction($this->fragment);
        return $ref->getAttributes($name, $flags);
    }
}
