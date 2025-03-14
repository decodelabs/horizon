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

interface Body
{
    public mixed $content { get; set; }

    /**
     * @var array<string,PriorityMarkup<Tag>>
     */
    public array $bodyScripts { get; }

    /**
     * @var array<string,PriorityMarkup<Markup>>
     */
    public array $appendBody { get; }

    public Tag $bodyTag { get; set; }

    /**
     * @var ?Closure(mixed):Markup
     */
    public ?Closure $layout { get; set; }


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
     * @return $this
     */
    public function clearBodyScripts(): static;



    /**
     * @return $this
     */
    public function appendBody(
        string $key,
        Markup $value,
        int $priority = 0
    ): static;

    public function hasAppendBody(
        string $key
    ): bool;

    public function getAppendBody(
        string $key
    ): ?Markup;

    public function getAppendBodyPriority(
        string $key
    ): ?int;

    public function removeAppendBody(
        string $key
    ): static;

    public function clearAppendBody(): static;
}
