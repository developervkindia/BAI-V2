<?php

namespace App\Services;

class DocsHtmlSanitizerService
{
    /**
     * Allowed HTML tags and their attributes for document content.
     */
    private array $allowedTags = [
        'p', 'br', 'hr', 'span', 'div',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'del', 'ins',
        'sub', 'sup', 'small', 'mark', 'abbr', 'cite', 'code', 'kbd', 'samp', 'var',
        'blockquote', 'pre',
        'ul', 'ol', 'li',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption', 'colgroup', 'col',
        'a', 'img', 'figure', 'figcaption',
        'video', 'audio', 'source', 'iframe',
        'details', 'summary',
    ];

    private array $allowedAttributes = [
        '*' => ['class', 'style', 'id', 'data-doc-comment', 'data-mce-*', 'dir', 'lang', 'title'],
        'a' => ['href', 'target', 'rel'],
        'img' => ['src', 'alt', 'width', 'height', 'loading'],
        'td' => ['colspan', 'rowspan', 'headers'],
        'th' => ['colspan', 'rowspan', 'scope', 'headers'],
        'col' => ['span'],
        'colgroup' => ['span'],
        'iframe' => ['src', 'width', 'height', 'frameborder', 'allowfullscreen'],
        'source' => ['src', 'type'],
        'video' => ['src', 'controls', 'width', 'height', 'poster'],
        'audio' => ['src', 'controls'],
    ];

    /**
     * Sanitize HTML content from TinyMCE.
     */
    public function sanitize(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Use DOMPurify on the client side. Server-side, do basic cleanup:
        // Strip script/event handler attributes
        $html = preg_replace('/\s+on\w+\s*=\s*"[^"]*"/i', '', $html);
        $html = preg_replace('/\s+on\w+\s*=\s*\'[^\']*\'/i', '', $html);
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);

        return $html;
    }
}
