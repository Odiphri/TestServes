<?php

namespace App\Support;

final class QuestionHtml
{
    public static function sanitize(string $html): string
    {
        $allowedTags = '<p><br><strong><b><em><i><u><s><ol><ul><li><blockquote><code><pre><sub><sup><span>';
        $cleanHtml = strip_tags($html, $allowedTags);

        if (! class_exists(\DOMDocument::class)) {
            return $cleanHtml;
        }

        $previous = libxml_use_internal_errors(true);
        $document = new \DOMDocument;
        $document->loadHTML('<div>'.$cleanHtml.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        foreach ($document->getElementsByTagName('*') as $node) {
            while ($node->attributes && $node->attributes->length > 0) {
                $node->removeAttributeNode($node->attributes->item(0));
            }
        }

        $wrapper = $document->getElementsByTagName('div')->item(0);
        $output = '';

        if ($wrapper) {
            foreach ($wrapper->childNodes as $child) {
                $output .= $document->saveHTML($child);
            }
        }

        return trim($output);
    }
}
