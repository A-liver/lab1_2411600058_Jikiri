<?php

if (! function_exists('highlight_match')) {
    function highlight_match(string $text, ?string $query): string
    {
        $escaped = e($text);

        if (! $query || trim($query) === '') {
            return $escaped;
        }

        $pattern = '/(' . preg_quote(trim($query), '/') . ')/i';

        return preg_replace($pattern, '<mark>$1</mark>', $escaped);
    }
}