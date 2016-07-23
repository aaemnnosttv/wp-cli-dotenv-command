<?php

namespace WP_CLI_Dotenv\WP_CLI;

/**
 * Class AssocArgs
 * @package WP_CLI_Dotenv\WP_CLI
 *
 * @property-read $file
 * @property-read $fields
 * @property-read $keys
 */
class AssocArgs
{
    private $__args = [];

    protected $file = '.env';

    protected $fields = ['key', 'value'];

    protected $keys = [];

    public function __construct(array $args = [])
    {
        $this->__args = $args;
        $this->fill($args);
    }

    protected function fill(array $args)
    {
        foreach ($args as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * The original unmodified arguments
     * @return array
     */
    public function original()
    {
        return $this->__args;
    }

    public function toArray()
    {
        $args = get_object_vars($this);
        unset($args['__args']);

        return $args;
    }

    public function __get($prop)
    {
        return isset($this->$prop) ? $this->$prop : null;
    }
}