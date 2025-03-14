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
use DecodeLabs\Tagged\ContentCollection;
use DecodeLabs\Tagged\Element;
use DecodeLabs\Tagged\Markup;
use DecodeLabs\Tagged\Tag;

/**
 * @phpstan-require-implements Body
 */
trait BodyTrait
{
    use RenderableTrait;

    public mixed $content = null;

    /**
     * @var array<string,PriorityMarkup<Tag>>
     */
    protected(set) array $bodyScripts = [];

    /**
     * @var array<string,PriorityMarkup<Markup>>
     */
    protected(set) array $appendBody = [];

    public Tag $bodyTag;

    /**
     * @var ?Closure(mixed):mixed
     */
    public ?Closure $layout = null;

    public function __construct(
        mixed $content = null
    ) {
        $this->content = $content;
        $this->bodyTag = new Tag('body');
    }


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
     * @return $this
     */
    public function clearBodyScripts(): static
    {
        $this->bodyScripts = [];
        return $this;
    }





    /**
     * @return $this
     */
    public function appendBody(
        string $key,
        Markup $value,
        int $priority = 0
    ): static {
        if(!$value instanceof PriorityMarkup) {
            $value = new PriorityMarkup($value, $priority);
        }

        $this->appendBody[$key] = $value;
        return $this;
    }

    public function hasAppendBody(
        string $key
    ): bool {
        return isset($this->appendBody[$key]);
    }

    public function getAppendBody(
        string $key
    ): ?Markup {
        return ($this->appendBody[$key] ?? null)?->markup;
    }

    public function getAppendBodyPriority(
        string $key
    ): ?int {
        return ($this->appendBody[$key] ?? null)?->priority;
    }

    /**
     * @return $this
     */
    public function removeAppendBody(
        string $key
    ): static {
        unset($this->appendBody[$key]);
        return $this;
    }

    /**
     * @return $this
     */
    public function clearAppendBody(): static
    {
        $this->appendBody = [];
        return $this;
    }



    public function render(): Buffer
    {
        $content = $this->content;

        if($content instanceof Closure) {
            $content = $content($this);
        }

        /**
         * @var Buffer $output
         */
        $output = $this->bodyTag->renderWith(
            content: function() use($content) {
                // Content
                $content = ContentCollection::normalize($content, $this->renderPretty);

                // Layout
                if($this->layout) {
                    $content = ($this->layout)($content);
                }

                yield $content;

                // Append body
                if(!empty($this->appendBody)) {
                    uasort($this->appendBody, function($a, $b) {
                        return $a->priority <=> $b->priority;
                    });

                    foreach($this->appendBody as $tag) {
                        yield $tag->markup;
                    }
                }

                // Scripts
                if(!empty($this->bodyScripts)) {
                    uasort($this->bodyScripts, function($a, $b) {
                        return $a->priority <=> $b->priority;
                    });

                    foreach($this->bodyScripts as $tag) {
                        yield $tag->markup;
                    }
                }
            },
            pretty: $this->renderPretty
        ) ?? new Buffer('<body></body>');

        return $output;
    }
}
