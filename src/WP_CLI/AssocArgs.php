<?php

namespace WP_CLI_Dotenv\WP_CLI;

use Illuminate\Support\Collection;

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
    /**
     * User provided arguments.
     * @var array
     */
    protected $args;

    /**
     * Default arguments.
     * @var array
     */
    protected $defaults = [
        'file'   => '.env',
        'fields' => ['key', 'value'],
        'keys'   => [],
    ];

    /**
     * AssocArgs constructor.
     *
     * @param array $args
     */
    public function __construct(array $args = [])
    {
        $this->args = $args;
    }

    /**
     * The original unmodified arguments.
     *
     * @return array
     */
    public function original()
    {
        return $this->args;
    }

    /**
     * Get the single array of arguments.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->defaults, $this->args);
    }

    /**
     * Magic getter.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __get($prop)
    {
        return Collection::make($this->toArray())->get($prop);
    }
}
