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
use DecodeLabs\Nuance\Dumpable;
use DecodeLabs\Nuance\Entity\NativeObject as NuanceEntity;
use DecodeLabs\Slingshot;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Component;
use DecodeLabs\Tagged\ContentCollection;
use DecodeLabs\Tagged\RenderableTrait;
use DecodeLabs\Tagged\Tag;
use ReflectionAttribute;
use ReflectionFunction;

class Fragment extends Tag implements
    Component,
    Dumpable
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



    public function toNuanceEntity(): NuanceEntity
    {
        if(!$this->rendering) {
            return parent::toNuanceEntity();
        }

        $entity = new NuanceEntity($this);
        $entity->setProperty('fragment', $this->fragment);
        $entity->setProperty('parameters', $this->parameters);
        $entity->setProperty('rendering', $this->rendering, 'private');
        $entity->setProperty('loaded', $this->loaded, 'private');
        return $entity;
    }
}
