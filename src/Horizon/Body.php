<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon;

use Closure;
use DecodeLabs\Elementary\Renderable;
use DecodeLabs\Horizon\Property\BodyScriptCollection;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Component\Fragment;
use DecodeLabs\Tagged\Markup;
use DecodeLabs\Tagged\PriorityMarkup;
use DecodeLabs\Tagged\Tag;

/**
 * @extends Renderable<Buffer>
 */
interface Body extends
    BodyScriptCollection,
    Renderable
{
    public mixed $content { get; set; }

    /**
     * @var array<string,PriorityMarkup<Markup>>
     */
    public array $appendBody { get; }

    public Tag $bodyTag { get; set; }

    /**
     * @var Fragment|Closure(mixed):(mixed)|null
     */
    public Fragment|Closure|null $layout { get; set; }



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

    public function renderContent(
        bool $pretty = false
    ): Buffer;
}
