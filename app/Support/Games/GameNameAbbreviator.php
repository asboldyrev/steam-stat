<?php

namespace App\Support\Games;

class GameNameAbbreviator
{
    /**
     * Генерирует аббревиатуру из названия игры.
     */
    public static function generateAbbreviation(string $name): string
    {
        $words = preg_split('/\s+/', trim($name));
        if (count($words) === 1) {
            return strtoupper(substr($words[0], 0, 3));
        }

        $abbr = '';
        foreach ($words as $word) {
            if (preg_match('/[A-Za-z]/', $word[0] ?? '')) {
                $abbr .= strtoupper($word[0]);
            }
            if (strlen($abbr) >= 3) {
                break;
            }
        }

        return strlen($abbr) > 0 ? $abbr : '???';
    }
}
