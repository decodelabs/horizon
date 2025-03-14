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
     * @var array<string,PriorityMarkup<Tag>>
     */
    protected(set) array $links = [];

    /**
     * @var array<string,PriorityMarkup<Tag>>
     */
    protected(set) array $scripts = [];

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
        array $attributes = [],
        string|int|float|bool|null ...$attributeList
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

        /** @var array<string,string|bool|int|float> $attributeList */
        $value->setAttributes($attributeList + $attributes);
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
     * @param array<string,string|int|float|bool> $attributes
     * @return $this
     */
    public function addLink(
        string $key,
        ?Tag $tag = null,
        int $priority = 0,
        array $attributes = [],
        string|int|float|bool|null ...$attributeList
    ): static {
        if($tag === null) {
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
     * @return $this
     */
    public function clearLinks(): static
    {
        $this->links = [];
        return $this;
    }





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
     * @return $this
     */
    public function clearScripts(): static
    {
        $this->scripts = [];
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
    public function clearAppendHead(): static
    {
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
            pretty: $this->renderPretty
        );
    }
}
