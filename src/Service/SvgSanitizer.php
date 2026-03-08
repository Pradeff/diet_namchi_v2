<?php

namespace App\Service;

use DOMDocument;
use DOMElement;

class SvgSanitizer
{
    private array $allowedTags = ['svg', 'path', 'circle', 'rect', 'polygon', 'line', 'polyline', 'g'];
    private array $allowedAttributes = [
        'svg' => ['width', 'height', 'viewBox', 'fill', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin'],
        'path' => ['d', 'fill', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin'],
        'circle' => ['cx', 'cy', 'r', 'fill', 'stroke', 'stroke-width'],
        'rect' => ['x', 'y', 'width', 'height', 'fill', 'stroke', 'stroke-width', 'rx', 'ry'],
        'polygon' => ['points', 'fill', 'stroke', 'stroke-width'],
        'line' => ['x1', 'y1', 'x2', 'y2', 'stroke', 'stroke-width'],
        'polyline' => ['points', 'fill', 'stroke', 'stroke-width'],
        'g' => ['fill', 'stroke', 'stroke-width', 'transform']
    ];

    public function sanitize(string $svgContent): string
    {
        if (empty($svgContent)) {
            return '';
        }

        $dom = new DOMDocument();
        $dom->loadXML($svgContent, LIBXML_NOERROR | LIBXML_NOWARNING);

        $this->cleanNode($dom->documentElement);

        return $dom->saveXML($dom->documentElement);
    }

    private function cleanNode(DOMElement $node): void
    {
        // Remove disallowed tags
        if (!in_array($node->tagName, $this->allowedTags)) {
            $node->parentNode->removeChild($node);
            return;
        }

        // Remove disallowed attributes
        $allowedAttrs = $this->allowedAttributes[$node->tagName] ?? [];
        $attributes = $node->attributes;
        for ($i = $attributes->length - 1; $i >= 0; $i--) {
            $attr = $attributes->item($i);
            if (!in_array($attr->name, $allowedAttrs)) {
                $node->removeAttribute($attr->name);
            }
        }

        // Remove script elements and event handlers
        $scripts = $node->getElementsByTagName('script');
        foreach ($scripts as $script) {
            $script->parentNode->removeChild($script);
        }

        // Recursively clean child nodes
        $children = $node->childNodes;
        for ($i = $children->length - 1; $i >= 0; $i--) {
            $child = $children->item($i);
            if ($child instanceof DOMElement) {
                $this->cleanNode($child);
            }
        }
    }
}
