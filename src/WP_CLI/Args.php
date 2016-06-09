<?php

namespace WP_CLI_Dotenv\WP_CLI;

trait Args
{
    /**
     * @var AssocArgs
     */
    protected $args;

    /**
     * @param $args array All arguments passed to the sub-command method
     */
    protected function init_args($args)
    {
        $this->args = new AssocArgs($args[1]);
    }

    /**
     * @param      $key
     * @param null $default
     *
     * @return mixed
     */
    protected function get_flag($key, $default = null)
    {
        return \WP_CLI\Utils\get_flag_value($this->args->original(), $key, $default);
    }
}
