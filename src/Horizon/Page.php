<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon;

use DecodeLabs\Coercion;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Tag;
use DecodeLabs\Zest\Manifest;

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

    public function __construct(
        mixed $content = null
    ) {
        $this->__constructHead();
        $this->__constructBody($content);
        $this->htmlTag = new Tag('html', ['lang' => 'en']);
    }


    /**
     * @return $this
     */
    public function importZestManifest(
        Manifest $manifest
    ): static {
        foreach($manifest->getCssData() as $file => $attributes) {
            if(!str_starts_with($file, '/')) {
                $file = '/'.$file;
            }

            /** @var array<string,string|bool|int|float> $attributes */
            $this->addLink(
                key: 'zest:'.$file,
                rel: 'stylesheet',
                href: $file,
                attributes: $attributes
            );
        }

        foreach($manifest->getHeadJsData() as $file => $attributes) {
            if(!str_starts_with($file, '/')) {
                $file = '/'.$file;
            }

            /** @var array<string,string|bool|int|float> $attributes */
            $this->addScript(
                key: 'zest:'.$file,
                src: $file,
                attributes: $attributes
            );
        }

        foreach($manifest->getBodyJsData() as $file => $attributes) {
            if(!str_starts_with($file, '/')) {
                $file = '/'.$file;
            }

            /** @var array<string,string|bool|int|float> $attributes */
            $this->addBodyScript(
                key: 'zest:'.$file,
                src: $file,
                attributes: $attributes
            );
        }

        return $this;
    }



    public function render(): Buffer
    {
        $body = $this->renderBody();

        /**
         * @var Buffer $buffer
         */
        $buffer = $this->htmlTag->renderWith(
            content: [
                $this->renderHead(),
                $body
            ],
            pretty: $this->renderPretty
        ) ?? new Buffer('<html><head></head><body></body></html>');

        $buffer->prepend('<!DOCTYPE html>'."\n");
        return $buffer;
    }
}
