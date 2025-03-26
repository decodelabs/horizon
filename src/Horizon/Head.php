<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon;

use Closure;
use DecodeLabs\Elementary\Renderable;
use DecodeLabs\Horizon\Property\LinkCollection;
use DecodeLabs\Horizon\Property\MetaCollection;
use DecodeLabs\Horizon\Property\ScriptCollection;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Markup;
use DecodeLabs\Tagged\PriorityMarkup;
use DecodeLabs\Tagged\Tag;

/**
 * @extends Renderable<Buffer>
 */
interface Head extends
    LinkCollection,
    MetaCollection,
    ScriptCollection,
    Renderable
{
    public string $charset { get; set; }

    public string|Closure|null $rawTitle { get; set; }
    public ?Closure $titleDecorator { get; set; }
    public string $title { get; set(string|Closure|null $value); }

    public ?string $base { get; set; }
    public ?string $baseTarget { get; set; }

    /**
     * @var array<string,PriorityMarkup<Markup>>
     */
    public array $appendHead { get; }

    public Tag $headTag { get; set; }


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
