<?php

namespace WP_CLI_Dotenv_Command;

use Illuminate\Support\Collection;

class Dotenv_Lines extends Collection
{
    /**
     * Single line format
     */
    const LINE_FORMAT = '%s=%s';

    /**
     * Pattern to match a var definition
     */
    const PATTERN_KEY_CAPTURE_FORMAT = '/^%s(\s+)?=/';

    const QUOTE_SINGLE = '\'';

    const QUOTE_DOUBLE = '"';

    /**
     * [fromFile description]
     * @param  [type] $filepath [description]
     * @return [type]           [description]
     */
    public static function fromFile($filepath)
    {
        return (new static(file($filepath, FILE_IGNORE_NEW_LINES)))
            ->map(function ($lineText) {
                return new Dotenv_Line($lineText);
            });
    }

    public function toString()
    {
        return $this->map(function ($line) {
            return $line->toString();
        })->implode(PHP_EOL);
    }

    public function pairs()
    {
        return $this->filter(function ($line) {
            return $line->isPair();
        });
    }

    /**
     * Trim surrounding quotes from a string
     *
     * @param $string
     *
     * @return string
     */
    public static function clean_quotes($string)
    {
        $first_char = substr((string) $string,  0, 1);
        $last_char  = substr((string) $string, -1, 1);

        /**
         * Test the first and last character for quote type
         */
        if ($first_char === $last_char && in_array($first_char, [self::QUOTE_SINGLE, self::QUOTE_DOUBLE])) {
            return trim($string, $first_char);
        }

        return $string;
    }
}
