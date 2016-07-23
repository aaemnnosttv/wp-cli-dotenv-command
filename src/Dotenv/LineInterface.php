<?php

namespace WP_CLI_Dotenv\Dotenv;

interface LineInterface
{
    /**
     * @return string
     */
    public function key();

    /**
     * @return string
     */
    public function value();

    /**
     * @return string
     */
    public function toString();
}