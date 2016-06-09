<?php

namespace WP_CLI_Dotenv\WP_CLI;

use WP_CLI;
use WP_CLI_Command;

/**
 * Manage WordPress salts in .env format
 */
class SaltsCommand extends WP_CLI_Command
{
    use Args;

    /**
     * Fetch some fresh salts and add them to the environment file if they do not already exist
     *
     * [--file=<path-to-dotenv>]
     * : Path to the environment file.  Default: '.env'
     *
     * @synopsis [--file=<path-to-dotenv>]
     *
     * @when before_wp_load
     *
     * @param $_
     * @param $assoc_args
     */
    function generate($_, $assoc_args)
    {
        $this->init_args(func_get_args());
        $dotenv = get_dotenv_for_write_or_fail($this->args->file);
        $set    = $skipped = [];

        try {
            $salts = Salts::fetch_array();
        } catch (\Exception $e) {
            WP_CLI::error($e->getMessage());

            return;
        }

        foreach ($salts as $salt) {
            list($key, $value) = $salt;

            if ($dotenv->has_key($key)) {
                WP_CLI::line("The '$key' already exists, skipping.");
                $skipped[] = $key;
                continue;
            }

            $dotenv->set($key, $value);
            $set[] = $key;
        }

        $dotenv->save();

        if (count($set) === count($salts)) {
            WP_CLI::success('Salts generated.');
        } elseif ($set) {
            WP_CLI::success(count($set) . ' salts set.');
        }

        if ($skipped) {
            WP_CLI::line('Some keys were already defined in the environment file.');
            WP_CLI::line("Use 'dotenv salts regenerate' to update them.");
        }
    }

    /**
     * Regenerate salts for the environment file
     *
     * [--file=<path-to-dotenv>]
     * : Path to the environment file.  Default: '.env'
     *
     * @synopsis [--file=<path-to-dotenv>]
     *
     * @when before_wp_load
     *
     * @param $_
     * @param $assoc_args
     */
    function regenerate($_, $assoc_args)
    {
        $this->init_args(func_get_args());
        $dotenv = get_dotenv_for_write_or_fail($this->args->file);

        try {
            $salts = Salts::fetch_array();
        } catch (\Exception $e) {
            WP_CLI::error($e->getMessage());

            return;
        }

        foreach ($salts as $salt) {
            list($key, $value) = $salt;
            $dotenv->set($key, $value);
        }

        $dotenv->save();

        WP_CLI::success('Salts regenerated.');
    }

}