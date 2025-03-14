<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon;

use Closure;
use DecodeLabs\Coercion;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Element;
use DecodeLabs\Tagged\Markup;
use DecodeLabs\Tagged\Tag;

/**
 * @phpstan-require-implements Head
 */
trait HeadTrait
{
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
     * @var array<string,Tag>
     */
    protected(set) array $meta = [];

    /**
     * @var array<string,PriorityMarkup>
     */
    protected(set) array $appendHead = [];

    public Tag $headTag;

    public function __construct()
    {
        $this->headTag = new Tag('head');
    }


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
     * @param array<string,string|int|float|bool> $attributes
     * @return $this
     */
    public function setMeta(
        string $key,
        string|Tag $value,
        array $attributes = []
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

        $value->setAttributes($attributes);
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
    public function clearAppendHead(): static {
        $this->appendHead = [];
        return $this;
    }




    public function render(): Buffer
    {
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

                // Scripts

                // Meta
                foreach($this->meta as $tag) {
                    yield $tag;
                }

                // Append head
                uasort($this->appendHead, function($a, $b) {
                    return $a->priority <=> $b->priority;
                });

                foreach($this->appendHead as $tag) {
                    yield $tag->markup;
                }
            },
            pretty: $this->renderPretty
        );
    }
}
