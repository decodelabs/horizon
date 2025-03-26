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
 * @phpstan-require-implements BodyScriptCollection
 */
trait BodyScriptCollectionTrait
{
    /**
     * @var array<string,PriorityMarkup<Tag>>
     */
    protected(set) array $bodyScripts = [];

    /**
     * @param array<string,string|int|float|bool> $attributes
     * @return $this
     */
    public function addBodyScript(
        string $key,
        ?Tag $tag = null,
        int $priority = 0,
        mixed $script = null,
        array $attributes = [],
        string|int|float|bool|null ...$attributeList
    ): static {
        if($tag === null) {
            /** @var array<string,string|int|float|bool|null> $attributeList */
            $tag = new Element('script', $script, $attributeList + $attributes);
        }

        $this->bodyScripts[$key] = new PriorityMarkup($tag, $priority);
        return $this;
    }

    public function hasBodyScript(
        string $key
    ): bool {
        return isset($this->bodyScripts[$key]);
    }

    public function getBodyScript(
        string $key
    ): ?Tag {
        return ($this->bodyScripts[$key] ?? null)?->markup;
    }

    public function getBodyScriptPriority(
        string $key
    ): ?int {
        return ($this->bodyScripts[$key] ?? null)?->priority;
    }

    /**
     * @return $this
     */
    public function removeBodyScript(
        string $key
    ): static {
        unset($this->bodyScripts[$key]);
        return $this;
    }

    /**
     * @return array<string,Tag>
     */
    public function getBodyScripts(): array
    {
        uasort($this->bodyScripts, function($a, $b) {
            return $a->priority <=> $b->priority;
        });

        return array_map(function($entry) {
            return $entry->markup;
        }, $this->bodyScripts);
    }

    /**
     * @return $this
     */
    public function clearBodyScripts(): static
    {
        $this->bodyScripts = [];
        return $this;
    }
}
