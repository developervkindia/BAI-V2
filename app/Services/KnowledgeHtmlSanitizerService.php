<?php

namespace App\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class KnowledgeHtmlSanitizerService
{
    private ?HtmlSanitizer $sanitizer = null;

    public function sanitize(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        return $this->sanitizer()->sanitize($html);
    }

    private function sanitizer(): HtmlSanitizer
    {
        if ($this->sanitizer === null) {
            $config = (new HtmlSanitizerConfig)
                ->allowSafeElements()
                ->allowRelativeMedias(true)
                ->allowMediaSchemes(['https', 'http']);

            $this->sanitizer = new HtmlSanitizer($config);
        }

        return $this->sanitizer;
    }
}
