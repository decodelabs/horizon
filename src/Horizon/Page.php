<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon;

use DecodeLabs\Archetype;
use DecodeLabs\Coercion;
use DecodeLabs\Elementary\Renderable;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Component\Fragment;
use DecodeLabs\Tagged\Tag;

/**
 * @implements Renderable<Buffer>
 */
class Page implements
    Head,
    Body,
    Renderable
{
    use HeadTrait {
        HeadTrait::__construct as __constructHead;
        HeadTrait::render as renderHead;
    }

    use BodyTrait {
        BodyTrait::__construct as __constructBody;
        BodyTrait::render as renderBody;
    }

    use RenderableTrait;

    public string $language {
        get {
            return Coercion::toString(
                $this->htmlTag->getAttribute('lang') ?? 'en'
            );
        }
        set(string $value) {
            $this->htmlTag->setAttribute('lang', $value);
        }
    }

    public Tag $htmlTag;

    public static function fromFragment(
        string $fragment,
        mixed ...$parameters
    ): Page {
        return new Page(
            new Fragment($fragment, ...$parameters)
        );
    }

    public function __construct(
        mixed $content = null
    ) {
        if($content instanceof Fragment) {
            $content->slingshot->addType($this, Page::class);
        }

        $this->__constructHead();
        $this->__constructBody($content);
        $this->htmlTag = new Tag('html', ['lang' => 'en']);
    }


    public function render(
        bool $pretty = false
    ): Buffer {
        $body = $this->renderBody($pretty);

        /**
         * @var Buffer $buffer
         */
        $buffer = $this->htmlTag->renderWith(
            content: [
                $this->renderHead($pretty),
                $body
            ],
            pretty: $pretty
        ) ?? new Buffer('<html><head></head><body></body></html>');

        $buffer->prepend('<!DOCTYPE html>'."\n");
        return $buffer;
    }


    /**
     * @return $this
     */
    public function decorate(
        string|Decorator $decorator,
        mixed ...$parameters
    ): static {
        if(is_string($decorator)) {
            $class = Archetype::resolve(Decorator::class, ucfirst($decorator));
            $decorator = new $class();
        }

        $decorator->decorate($this, ...$parameters);
        return $this;
    }
}
