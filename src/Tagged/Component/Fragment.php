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
    private bool $rendering = false;

    public Slingshot $slingshot {
        get => $this->slingshot ??= new Slingshot();
    }

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
        $this->rendering = true;

        $slingshot = $this->slingshot;
        $slingshot->addParameters($this->parameters);

        /** @var array<string,mixed> $parameters */
        $output = $slingshot->invoke(
            $this->fragment,
            $parameters
        );

        $pretty =
            ($parameters['pretty'] ?? false) === true ||
            ($this->parameters['pretty'] ?? false) === true;

        $output = ContentCollection::normalize($output, $pretty);

        $this->rendering = false;
        return $output;
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


    /**
     * Dump for glitch
     */
    public function glitchDump(): iterable
    {
        if(!$this->rendering) {
            return parent::glitchDump();
        }

        yield 'properties' => [
            'fragment' => $this->fragment,
            'parameters' => $this->parameters,
            '!rendering' => $this->rendering,
            '!loaded' => $this->loaded
        ];
    }
}
