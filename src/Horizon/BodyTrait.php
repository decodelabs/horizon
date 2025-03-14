<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon;

use Closure;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\ContentCollection;
use DecodeLabs\Tagged\Tag;
use Generator;

/**
 * @phpstan-require-implements Body
 */
trait BodyTrait
{
    use RenderableTrait;

    public mixed $content = null;
    public Tag $bodyTag;

    public function __construct(
        mixed $content = null
    ) {
        $this->content = $content;
        $this->bodyTag = new Tag('body');
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
            content: ContentCollection::normalize($content, $this->renderPretty),
            pretty: $this->renderPretty
        ) ?? new Buffer('<body></body>');

        return $output;
    }
}
