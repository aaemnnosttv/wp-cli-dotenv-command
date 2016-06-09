<?php

namespace WP_CLI_Dotenv_Command;

use Illuminate\Support\Collection;

class Dotenv_Line
{
    /**
     * Single line format
     */
    const FORMAT = '%s=%s';

    /**
     * Pattern to match a var definition
     */
    const PATTERN_KEY_CAPTURE_FORMAT = '/^%s(\s+)?=/';

    const QUOTE_SINGLE = '\'';

    const QUOTE_DOUBLE = '"';

    protected $text;


    public function __construct($text)
    {
        $this->text = $text;
    }

    public function key()
    {
        $pieces = explode('=', $this->text, 2);
        $pieces = array_map('trim', $pieces);

        return reset($pieces);
    }

    public function value()
    {
        $pieces = explode('=', $this->text, 2);
        $pieces = array_map('trim', $pieces);

        if (is_string(end($pieces))) {
            return static::clean_quotes(end($pieces));
        }

        return end($pieces);
    }

    public function isPair()
    {
        $pieces = explode('=', $this->text, 2);
        $pieces = array_map('trim', $pieces);

        return 2 === count($pieces);
    }

    public function toString()
    {
        if ($this->isPair()) {
            return sprintf(static::FORMAT, $this->key(), $this->value());
        }

        return $this->text;
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
