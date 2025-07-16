<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon\Property;

use DecodeLabs\Tagged\Element;
use DecodeLabs\Tagged\PriorityMarkup;
use DecodeLabs\Tagged\Tag;

/**
 * @phpstan-require-implements LinkCollection
 */
trait LinkCollectionTrait
{
    /**
     * @var array<string,PriorityMarkup<Tag>>
     */
    public protected(set) array $links = [];

    /**
     * @param array<string,string|int|float|bool|null> $attributes
     * @return $this
     */
    public function addLink(
        string $key,
        ?Tag $tag = null,
        int $priority = 0,
        array $attributes = [],
        string|int|float|bool|null ...$attributeList
    ): static {
        if ($tag === null) {
            /** @var array<string,string|int|float|bool|null> $attributeList */
            $tag = new Element('link', null, $attributeList + $attributes);
        }

        $this->links[$key] = new PriorityMarkup($tag, $priority);
        return $this;
    }

    public function hasLink(
        string $key
    ): bool {
        return isset($this->links[$key]);
    }

    public function getLink(
        string $key
    ): ?Tag {
        return ($this->links[$key] ?? null)?->markup;
    }

    public function getLinkPriority(
        string $key
    ): ?int {
        return ($this->links[$key] ?? null)?->priority;
    }

    /**
     * @return $this
     */
    public function removeLink(
        string $key
    ): static {
        unset($this->links[$key]);
        return $this;
    }

    /**
     * @return array<string,Tag>
     */
    public function getLinks(): array
    {
        uasort($this->links, function ($a, $b) {
            return $a->priority <=> $b->priority;
        });

        return array_map(function ($entry) {
            return $entry->markup;
        }, $this->links);
    }

    /**
     * @return $this
     */
    public function clearLinks(): static
    {
        $this->links = [];
        return $this;
    }
}
