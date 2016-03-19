<?php

namespace WP_CLI_Dotenv_Command;

use WP_CLI;
use WP_CLI_Command;
use WP_CLI\Formatter;

/**
 * Manage a .env file
 */
class Dotenv_Command extends WP_CLI_Command
{
    use DotenvArgs;
    
    /**
     * Initialize the environment file
     *
     * [--file=<path-to-dotenv>]
     * : Path to the environment file.  Default: '.env'
     *
     * [--with-salts]
     * : Additionally, generate and define keys for salts
     *
     * [--template=<template-name>]
     * : Path to a template to use to interactively set values
     *
     * [--interactive]
     * : Set new values from the template interactively with prompts for each key-value pair
     *
     * [--force]
     * : Overwrite existing destination file if it exists
     *
     * @synopsis [--file=<path-to-dotenv>] [--with-salts] [--template=<template-name>] [--interactive] [--force]
     *
     * @when before_wp_load
     *
     * @param $_
     * @param $assoc_args
     */
    public function init($_, $assoc_args)
    {
        $this->init_args(func_get_args());
        $filepath = get_filepath($this->args->file);

        if (file_exists($filepath) && ! $this->get_flag('force')) {
            WP_CLI::error("Environment file already exists at: $filepath");

            return;
        }

        $dotenv = Dotenv_File::create($filepath);

        if ( ! $dotenv->exists()) {
            WP_CLI::error('Failed to create environment file at: ' . $dotenv->get_filepath());

            return;
        }

        if ($this->args->template) {
            $this->init_from_template($dotenv, $this->args->template);
        }

        if ($this->get_flag('with-salts')) {
            WP_CLI::run_command(['dotenv', 'salts', 'generate'], ['file' => $dotenv->get_filepath()]);
        }

        WP_CLI::success("$filepath created.");
    }

    /**
     * @param $dotenv
     * @param $template
     */
    protected function init_from_template(Dotenv_File $dotenv, $template)
    {
        if ( ! $dotenv->is_writable()) {
            WP_CLI::error('Environment file is not readable at: ' . $dotenv->get_filepath());

            return;
        }

        $template_path = get_filepath($template);

        try {
            $dotenv_template = Dotenv_File::at($template_path);
        } catch (\Exception $e) {
            WP_CLI::error("Template file is not readable at: $template_path");

            return;
        }

        WP_CLI::line('Initializing from template: ' . $dotenv_template->get_filepath());

        copy($dotenv_template->get_filepath(), $dotenv->get_filepath());

        // we can't use WP-CLI --prompt because we're working off the template, not the synopsis
        if ( ! $interactive = $this->get_flag('interactive')) {
            return;
        }

        $dotenv->load(); // reload the new copied data from template

        WP_CLI::line();
        WP_CLI::line('Interactive init');
        WP_CLI::line('Specify a new value for each key, or leave blank to keep the current value.');
        WP_CLI::line();
        
        $this->prompt_all($dotenv);
    }

    /**
     * Set a value in the environment file for a given key.
     * Updates an existing value or creates a new entry.
     *
     * [--file=<path-to-dotenv>]
     * : Path to the environment file.  Default: '.env'
     *
     * @synopsis <key> <value>
     *
     * @when     before_wp_load
     *
     * @param $_
     * @param $assoc_args
     */
    public function set($_, $assoc_args)
    {
        $this->init_args(func_get_args());
        list($key, $value) = $_;

        $dotenv = get_dotenv_for_write_or_fail($this->args->file);
        $dotenv->set($key, $value);
        $dotenv->save();

        WP_CLI::success("'$key' set.");
    }

    /**
     * Get the value for a given key from the environment file
     *
     * [--file=<path-to-dotenv>]
     * : Path to the environment file.  Default: '.env'
     *
     * @synopsis <key>
     *
     * @when     before_wp_load
     *
     * @param $_
     * @param $assoc_args
     */
    public function get($_, $assoc_args)
    {
        $this->init_args(func_get_args());
        list($key) = $_;

        $dotenv = get_dotenv_for_read_or_fail($this->args->file);
        $value  = $dotenv->get($key);

        if ($value || ! in_array($value, [false, null], true)) {
            WP_CLI::line($value);

            return;
        }

        WP_CLI::error("Key '$key' not found.");
    }

    /**
     * Delete a definition from the environment file
     *
     * [--file=<path-to-dotenv>]
     * : Path to the environment file.  Default: '.env'
     *
     * @synopsis <key>...
     *
     * @when     before_wp_load
     *
     * @param $_
     * @param $assoc_args
     */
    public function delete($_, $assoc_args)
    {
        $this->init_args(func_get_args());
        $dotenv = get_dotenv_for_write_or_fail($this->args->file);

        foreach ($_ as $key) {
            if ($result = $dotenv->remove($key)) {
                WP_CLI::success("Removed '$key'");
            } else {
                WP_CLI::warning("No line found for key: '$key'");
            }
        }

        $dotenv->save();
    }


    /**
     * List the defined variables from the environment file
     *
     * [--format=<format>]
     * : Accepted values: table, csv, json, count. Default: table
     *
     * [--file=<path-to-dotenv>]
     * : Path to the environment file.  Default: '.env'
     *
     * @subcommand list
     * @when       before_wp_load
     *
     * @param $_
     * @param $assoc_args
     */
    public function _list($_, $assoc_args)
    {
        $this->init_args(func_get_args());
        $dotenv = get_dotenv_for_read_or_fail($this->args->file);
        $keys   = is_string($this->args->keys) ? explode(',', $this->args->keys) : $this->args->keys;
        $items  = [];

        foreach ($dotenv->get_pairs() as $key => $value) {
            // Skip if not requested
            if ( is_array($keys) && ! in_array($key, $keys)) {
                continue;
            }

            $items[] = (object) compact('key', 'value');
        }

        $fields    = is_string($this->args->fields) ? explode(',', $this->args->fields) : $this->args->fields;
        $args      = $this->args->toArray();
        $formatter = new Formatter($args, $fields);
        $formatter->display_items($items);
    }

    /**
     * Iterate over each line and prompt for a new value
     *
     * @param Dotenv_File $dotenv
     *
     * @return int
     */
    protected function prompt_all(Dotenv_File $dotenv)
    {
        $dotenv->transform(function ($line) use ($dotenv) {
            if ( ! $pair = $dotenv->get_pair_for_line($line)) {
                return $line;
            }

            $user_value = \cli\prompt($pair[ 'key' ], $pair[ 'value' ]);

            if ( ! strlen($user_value)) {
                return $line;
            }

            return format_line($pair[ 'key' ], $user_value);
        })->save()
        ;
    }
}
