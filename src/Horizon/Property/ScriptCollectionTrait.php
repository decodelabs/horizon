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
 * @phpstan-require-implements ScriptCollection
 */
trait ScriptCollectionTrait
{
    /**
     * @var array<string,PriorityMarkup<Tag>>
     */
    protected(set) array $scripts = [];

    /**
     * @param array<string,string|int|float|bool|null> $attributes
     * @return $this
     */
    public function addScript(
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

        $this->scripts[$key] = new PriorityMarkup($tag, $priority);
        return $this;
    }

    public function hasScript(
        string $key
    ): bool {
        return isset($this->scripts[$key]);
    }

    public function getScript(
        string $key
    ): ?Tag {
        return ($this->scripts[$key] ?? null)?->markup;
    }

    public function getScriptPriority(
        string $key
    ): ?int {
        return ($this->scripts[$key] ?? null)?->priority;
    }

    /**
     * @return $this
     */
    public function removeScript(
        string $key
    ): static {
        unset($this->scripts[$key]);
        return $this;
    }

    /**
     * @return array<string,Tag>
     */
    public function getScripts(): array
    {
        uasort($this->scripts, function($a, $b) {
            return $a->priority <=> $b->priority;
        });

        return array_map(function($entry) {
            return $entry->markup;
        }, $this->scripts);
    }

    /**
     * @return $this
     */
    public function clearScripts(): static
    {
        $this->scripts = [];
        return $this;
    }
}
