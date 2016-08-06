<?php

namespace WP_CLI_Dotenv\Dotenv;

/**
 * Class KeyValue
 * @package WP_CLI_Dotenv\Dotenv
 */
class KeyValue implements LineInterface
{
    /**
     * Var key
     * @var string
     */
    protected $key;

    /**
     * Var value
     * @var string
     */
    protected $value;

    /**
     * Quote character to wrap the value with
     * @var string
     */
    protected $quote;

    /**
     * Single line format
     */
    const FORMAT = '{key}={quote}{value}{quote}';

    /**
     * KeyValue constructor.
     *
     * @param $key
     * @param $value
     * @param $quote
     */
    public function __construct($key, $value, $quote)
    {
        $this->key = $key;
        $this->value = $value;
        $this->quote = $quote;
    }

    /**
     * Get the key.
     *
     * @return string
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * Get the value, unwrapped.
     *
     * @return string
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * Assemble the instance into its single-line string format.
     *
     * @return string
     */
    public function toString()
    {
        return str_replace([
                '{key}',
                '{value}',
                '{quote}'
            ],
            [
                $this->key,
                $this->value,
                $this->quote
            ],
            static::FORMAT
        );
    }
}