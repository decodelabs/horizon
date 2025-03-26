<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon\Property;

use DecodeLabs\Coercion;
use DecodeLabs\Tagged\Element;
use DecodeLabs\Tagged\Tag;

/**
 * @phpstan-require-implements MetaCollection
 */
trait MetaCollectionTrait
{
    /**
     * @var array<string,Tag>
     */
    protected(set) array $meta = [];

    /**
     * @param iterable<string,string|Tag> $meta
     * @return $this
     */
    public function applyMeta(
        iterable $meta
    ): static {
        foreach($meta as $key => $value) {
            $this->setMeta($key, $value);
        }

        return $this;
    }

    /**
     * @param array<string,string|int|float|bool|null> $attributes
     * @return $this
     */
    public function setMeta(
        string $key,
        string|Tag $value,
        array $attributes = [],
        string|int|float|bool|null ...$attributeList
    ): static {
        $parts = explode('=', $key, 2);
        $key = array_pop($parts);
        $nameKey = array_shift($parts) ?? 'name';

        if(is_string($value)) {
            $value = new Element('meta', null, [
                $nameKey => $key,
                'content' => $value
            ]);
        }

        /** @var array<string,string|bool|int|float> $attributeList */
        $value->setAttributes($attributeList + $attributes);
        $this->meta[$key] = $value;
        return $this;
    }

    public function hasMeta(
        string $key
    ): bool {
        return isset($this->meta[$key]);
    }

    public function getMeta(
        string $key
    ): ?Tag {
        return $this->meta[$key] ?? null;
    }

    public function getMetaValue(
        string $key
    ): ?string {
        return Coercion::tryString(
            ($this->meta[$key] ?? null)?->getAttribute('content')
        );
    }

    /**
     * @return $this
     */
    public function removeMeta(
        string $key
    ): static {
        unset($this->meta[$key]);
        return $this;
    }

    /**
     * @return $this
     */
    public function clearMeta(): static {
        $this->meta = [];
        return $this;
    }
}
