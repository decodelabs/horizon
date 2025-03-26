<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon;

use Closure;
use DecodeLabs\Coercion;
use DecodeLabs\Horizon\Property\LinkCollectionTrait;
use DecodeLabs\Horizon\Property\MetaCollectionTrait;
use DecodeLabs\Horizon\Property\ScriptCollectionTrait;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Element;
use DecodeLabs\Tagged\Markup;
use DecodeLabs\Tagged\PriorityMarkup;
use DecodeLabs\Tagged\Tag;

/**
 * @phpstan-require-implements Head
 */
trait HeadTrait
{
    use LinkCollectionTrait;
    use MetaCollectionTrait;
    use ScriptCollectionTrait;
    use RenderableTrait;

    public string $charset = 'utf-8';

    public string|Closure|null $rawTitle = null;
    public ?Closure $titleDecorator = null;

    public string $title {
        get {
            $title = Coercion::tryString($this->rawTitle);

            if($this->titleDecorator === null) {
                return $title ?? 'untitled';
            }

            return Coercion::tryString(($this->titleDecorator)($title)) ?? $title ?? 'untitled';
        }
        set(string|Closure|null $value) {
            $this->rawTitle = $value;
        }
    }

    public ?string $base = null;
    public ?string $baseTarget = null;

    /**
     * @var array<string,PriorityMarkup<Markup>>
     */
    protected(set) array $appendHead = [];

    public Tag $headTag;

    public function __construct()
    {
        $this->headTag = new Tag('head');
    }

    /**
     * @return $this
     */
    public function appendHead(
        string $key,
        Markup $value,
        int $priority = 0
    ): static {
        if(!$value instanceof PriorityMarkup) {
            $value = new PriorityMarkup($value, $priority);
        }

        $this->appendHead[$key] = $value;
        return $this;
    }

    public function hasAppendHead(
        string $key
    ): bool {
        return isset($this->appendHead[$key]);
    }

    public function getAppendHead(
        string $key
    ): ?Markup {
        return ($this->appendHead[$key] ?? null)?->markup;
    }

    public function getAppendHeadPriority(
        string $key
    ): ?int {
        return ($this->appendHead[$key] ?? null)?->priority;
    }

    /**
     * @return $this
     */
    public function removeAppendHead(
        string $key
    ): static {
        unset($this->appendHead[$key]);
        return $this;
    }

    /**
     * @return $this
     */
    public function clearAppendHead(): static
    {
        $this->appendHead = [];
        return $this;
    }




    public function render(
        bool $pretty = false
    ): Buffer {
        return $this->headTag->renderWith(
            content: function() {
                // Charset
                yield new Element('meta', null, ['charset' => $this->charset]);

                // Title
                yield new Element('title', $this->title);

                if(
                    $this->base !== null ||
                    $this->baseTarget !== null
                ) {
                    yield new Element('base', null, [
                        'href' => $this->base,
                        'target' => $this->baseTarget
                    ]);
                }

                // Links
                if(!empty($this->links)) {
                    uasort($this->links, function($a, $b) {
                        return $a->priority <=> $b->priority;
                    });

                    foreach($this->links as $tag) {
                        yield $tag->markup;
                    }
                }


                // Scripts
                if(!empty($this->scripts)) {
                    uasort($this->scripts, function($a, $b) {
                        return $a->priority <=> $b->priority;
                    });

                    foreach($this->scripts as $tag) {
                        yield $tag->markup;
                    }
                }


                // Meta
                foreach($this->meta as $tag) {
                    yield $tag;
                }


                // Append head
                if(!empty($this->appendHead)) {
                    uasort($this->appendHead, function($a, $b) {
                        return $a->priority <=> $b->priority;
                    });

                    foreach($this->appendHead as $tag) {
                        yield $tag->markup;
                    }
                }
            },
            pretty: $pretty
        );
    }
}
