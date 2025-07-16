<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon;

use Closure;
use DecodeLabs\Horizon\Property\BodyScriptCollectionTrait;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Component\Fragment;
use DecodeLabs\Tagged\ContentCollection;
use DecodeLabs\Tagged\Markup;
use DecodeLabs\Tagged\PriorityMarkup;
use DecodeLabs\Tagged\Tag;

/**
 * @phpstan-require-implements Body
 */
trait BodyTrait
{
    use BodyScriptCollectionTrait;
    use RenderableTrait;

    public mixed $content = null;

    /**
     * @var array<string,PriorityMarkup<Markup>>
     */
    public protected(set) array $appendBody = [];

    public Tag $bodyTag;

    /**
     * @var ?Closure(mixed $content):mixed
     */
    public Fragment|Closure|null $layout = null {
        get => $this->layout;

        /**
         * @param callable|Fragment|Closure(mixed $content):(mixed)|null $layout
         */
        set(callable|Fragment|Closure|null $layout) {
            if ($layout instanceof Fragment) {
                $layout->bind($this);
            } elseif (
                !$layout instanceof Closure &&
                is_callable($layout)
            ) {
                $layout = Closure::fromCallable($layout);
            }

            $this->layout = $layout;
        }
    }

    public function __construct(
        mixed $content = null
    ) {
        $this->content = $content;
        $this->bodyTag = new Tag('body');
    }



    /**
     * @return $this
     */
    public function appendBody(
        string $key,
        Markup $value,
        int $priority = 0
    ): static {
        if (!$value instanceof PriorityMarkup) {
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



    public function render(
        bool $pretty = false
    ): Buffer {
        $content = $this->content;

        if ($content instanceof Fragment) {
            $content->bind($this);
            $content = $content->render($pretty);
        } elseif ($content instanceof Closure) {
            $content = $content($this);
        }

        /** @var Buffer $output */
        $output = $this->bodyTag->renderWith(
            content: function () use ($content, $pretty) {
                // Content
                $content = ContentCollection::normalize($content, $pretty);

                // Layout
                if ($this->layout instanceof Fragment) {
                    $content = $this->layout->__invoke(
                        content: $content,
                        pretty: $pretty
                    );
                } elseif ($this->layout) {
                    $content = ($this->layout)($content);
                }

                yield $content;

                // Append body
                if (!empty($this->appendBody)) {
                    uasort($this->appendBody, function ($a, $b) {
                        return $a->priority <=> $b->priority;
                    });

                    foreach ($this->appendBody as $tag) {
                        yield $tag->markup;
                    }
                }

                // Scripts
                if (!empty($this->bodyScripts)) {
                    uasort($this->bodyScripts, function ($a, $b) {
                        return $a->priority <=> $b->priority;
                    });

                    foreach ($this->bodyScripts as $tag) {
                        yield $tag->markup;
                    }
                }
            },
            pretty: $pretty
        ) ?? new Buffer('<body></body>');

        return $output;
    }


    public function renderContent(
        bool $pretty = false
    ): Buffer {
        $content = $this->content;

        if ($content instanceof Fragment) {
            $content->bind($this);
            return $content->render($pretty) ?? new Buffer('');
        }

        if ($content instanceof Closure) {
            $content = ($content)($this);
        }

        return ContentCollection::normalize($content, $pretty);
    }
}
