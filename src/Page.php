<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon;

use DecodeLabs\Harvest;
use DecodeLabs\Harvest\Response;
use DecodeLabs\Harvest\ResponseProxy;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Tag;

class Page implements
    Head,
    Body,
    Renderable,
    ResponseProxy
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

    public Tag $htmlTag;

    public function __construct(
        mixed $content = null
    ) {
        $this->__constructHead();
        $this->__constructBody($content);
        $this->htmlTag = new Tag('html');
    }

    public function render(): Buffer
    {
        $body = $this->renderBody();

        $buffer = $this->htmlTag->renderWith(
            content: [
                $this->renderHead(),
                $body
            ],
            pretty: $this->renderPretty
        );

        $buffer->prepend('<!DOCTYPE html>'."\n");

        return $buffer;
    }

    public function toHttpResponse(): Response
    {
        return Harvest::html($this->render());
    }
}
