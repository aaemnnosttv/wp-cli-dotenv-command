<?php

namespace WP_CLI_Dotenv_Command;

class AssocArgs
{
    protected $file = '.env';

    protected $fields = ['key', 'value'];

    public function __construct(array $args = [])
    {
        $this->fill($args);
    }

    protected function fill(array $args)
    {
        foreach ($args as $key => $value) {
            $this->$key = $value;
        }
    }

    public function toArray()
    {
        return (array) $this;
    }

    public function __get($prop)
    {
        return isset($this->$prop) ? $this->$prop : null;
    }
}