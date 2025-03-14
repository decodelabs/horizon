<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon;

use Closure;
use DecodeLabs\Tagged\Markup;
use DecodeLabs\Tagged\Tag;

interface Head extends Renderable
{
    public string $charset { get; set; }

    public string|Closure|null $rawTitle { get; set; }
    public ?Closure $titleDecorator { get; set; }
    public string $title { get; set(string|Closure|null $value); }

    public ?string $base { get; set; }
    public ?string $baseTarget { get; set; }

    /**
     * @var array<string,Tag>
     */
    public array $meta { get; }

    /**
     * @var array<string,PriorityMarkup<Tag>>
     */
    public array $links { get; }

    /**
     * @var array<string,PriorityMarkup<Tag>>
     */
    public array $scripts { get; }

    /**
     * @var array<string,PriorityMarkup<Markup>>
     */
    public array $appendHead { get; }

    /**
     * @param iterable<string,string|Tag> $meta
     * @return $this
     */
    public function applyMeta(
        iterable $meta
    ): static;

    /**
     * @param array<string,string|int|float|bool> $attributes
     * @return $this
     */
    public function setMeta(
        string $key,
        string|Tag $value,
        array $attributes = [],
        string|int|float|bool|null ...$attributeList
    ): static;

    public function hasMeta(
        string $key
    ): bool;

    public function getMeta(
        string $key
    ): ?Tag;

    public function getMetaValue(
        string $key
    ): ?string;

    /**
     * @return $this
     */
    public function removeMeta(
        string $key
    ): static;

    /**
     * @return $this
     */
    public function clearMeta(): static;



    /**
     * @param array<string,string|int|float|bool> $attributes
     * @return $this
     */
    public function addLink(
        string $key,
        ?Tag $tag = null,
        int $priority = 0,
        array $attributes = [],
        string|int|float|bool|null ...$attributeList
    ): static;

    public function hasLink(
        string $key
    ): bool;

    public function getLink(
        string $key
    ): ?Tag;

    public function getLinkPriority(
        string $key
    ): ?int;

    /**
     * @return $this
     */
    public function removeLink(
        string $key
    ): static;

    /**
     * @return $this
     */
    public function clearLinks(): static;



    /**
     * @param array<string,string|int|float|bool> $attributes
     * @return $this
     */
    public function addScript(
        string $key,
        ?Tag $tag = null,
        int $priority = 0,
        mixed $script = null,
        array $attributes = [],
        string|int|float|bool|null ...$attributeList
    ): static;

    public function hasScript(
        string $key
    ): bool;

    public function getScript(
        string $key
    ): ?Tag;

    public function getScriptPriority(
        string $key
    ): ?int;

    /**
     * @return $this
     */
    public function removeScript(
        string $key
    ): static;

    /**
     * @return $this
     */
    public function clearScripts(): static;



    /**
     * @return $this
     */
    public function appendHead(
        string $key,
        Markup $value,
        int $priority = 0
    ): static;

    public function hasAppendHead(
        string $key
    ): bool;

    public function getAppendHead(
        string $key
    ): ?Markup;

    public function getAppendHeadPriority(
        string $key
    ): ?int;

    public function removeAppendHead(
        string $key
    ): static;

    public function clearAppendHead(): static;
}
