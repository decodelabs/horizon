<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon\Property;

use Closure;
use DecodeLabs\Tagged\PriorityMarkup;
use DecodeLabs\Tagged\Tag;

interface BodyScriptCollection
{
    /**
     * @var array<string,PriorityMarkup<Tag>>
     */
    public array $bodyScripts { get; }

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
    ): static;

    public function hasBodyScript(
        string $key
    ): bool;

    public function getBodyScript(
        string $key
    ): ?Tag;

    public function getBodyScriptPriority(
        string $key
    ): ?int;

    /**
     * @return $this
     */
    public function removeBodyScript(
        string $key
    ): static;

    /**
     * @return array<string,Tag>
     */
    public function getBodyScripts(): array;

    /**
     * @return $this
     */
    public function clearBodyScripts(): static;
}
