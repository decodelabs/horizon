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
                return $title;
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


    public Tag $headTag;

    public function __construct()
    {
        $this->headTag = new Tag('head');
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
            $this->meta[$key]?->getAttribute('content')
        );
    }




    public function render(): Buffer
    {
        return $this->headTag->renderWith(
            content: function() {
                // Charset
                yield new Element('meta', null, ['charset' => $this->charset]);

                // Title
                if(null !== ($title = $this->title)) {
                    yield new Element('title', $title);
                }

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
            },
            pretty: $this->renderPretty
        );
    }
}
