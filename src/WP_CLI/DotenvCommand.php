<?php

namespace WP_CLI_Dotenv\WP_CLI;

use WP_CLI;
use WP_CLI\Formatter;
use WP_CLI_Dotenv\Dotenv\File;

/**
 * Manage a .env file
 */
class DotenvCommand extends Command
{
    /**
     * Initialize the environment file.
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
     * @when before_wp_load
     *
     * @param $_
     * @param $assoc_args
     */
    public function init($_, $assoc_args)
    {
        $this->init_args(func_get_args());
        $path = $this->resolve_file_path();

        if (file_exists($path) && ! $this->get_flag('force')) {
            WP_CLI::error("Environment file already exists at: $path");

            return;
        }

        $env = File::create($path);

        if (! $env->exists()) {
            WP_CLI::error('Failed to create environment file at: ' . $env->path());

            return;
        }

        if ($this->args->template) {
            $this->init_from_template($env, $this->args->template);
        }

        if ($this->get_flag('with-salts')) {
            WP_CLI::run_command(['env', 'salts', 'generate'], ['file' => $env->path()]);
        }

        WP_CLI::success("$path created.");
    }

    /**
     * @param $env
     * @param $template
     */
    protected function init_from_template(File $env, $template)
    {
        if (! $env->is_writable()) {
            WP_CLI::error('Environment file is not readable at: ' . $env->path());

            return;
        }

        $template_path = $this->resolve_file_path($template);

        try {
            $env_template = File::at($template_path);
        } catch (\Exception $e) {
            WP_CLI::error("Template file is not readable at: $template_path");

            return;
        }

        WP_CLI::line('Initializing from template: ' . $env_template->path());

        copy($env_template->path(), $env->path());

        // we can't use WP-CLI --prompt because we're working off the template, not the synopsis
        if (! $this->get_flag('interactive')) {
            return;
        }

        $env->load(); // reload the new copied data from template

        WP_CLI::line();
        WP_CLI::line('Interactive init');
        WP_CLI::line('Specify a new value for each key, or leave blank to keep the current value.');
        WP_CLI::line();

        $this->prompt_all($env);
    }

    /**
     * Set a value in the environment file for a given key.
     * Updates an existing value or creates a new entry.
     *
     * <key>
     * : The var key
     *
     * <value>
     * : the value to set.
     *
     * [--file=<path-to-dotenv>]
     * : Path to the environment file.  Default: '.env'
     *
     * [--quote-single]
     * : Wrap the value with single quotes.
     *
     * [--quote-double]
     * : Wrap the value with double quotes.
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

        $env = $this->get_env_for_write_or_fail();
        $env->set($key, $value, $this->quote());
        $env->save();

        WP_CLI::success("'$key' set.");
    }

    /**
     * Get the quote to wrap the value with as set by the command flags.
     *
     * @return string
     */
    protected function quote()
    {
        if ($this->get_flag('quote-single')) {
            return "'";
        }

        if ($this->get_flag('quote-double')) {
            return '"';
        }

        return '';
    }

    /**
     * Get the value for a given key from the environment file
     *
     * <key>
     * : The variable name to retrieve the value for.
     *
     * [--file=<path-to-dotenv>]
     * : Path to the environment file.  Default: '.env'
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

        $env = $this->get_env_for_read_or_fail();

        if (! $env->has_key($key)) {
            WP_CLI::error("Key '$key' not found.");
            exit;
        }

        WP_CLI::line($env->get($key));
    }

    /**
     * Delete a definition from the environment file
     *
     * <key>...
     * : One or more keys to remove from the environment file.
     *
     * [--file=<path-to-dotenv>]
     * : Path to the environment file.  Default: '.env'
     *
     * @when     before_wp_load
     *
     * @param $_
     * @param $assoc_args
     */
    public function delete($_, $assoc_args)
    {
        $this->init_args(func_get_args());
        $env = $this->get_env_for_write_or_fail();

        foreach ($_ as $key) {
            if ($result = $env->remove($key)) {
                WP_CLI::success("Removed '$key'");
            } else {
                WP_CLI::warning("No line found for key: '$key'");
            }
        }

        $env->save();
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
        $env   = $this->get_env_for_read_or_fail();
        $vars  = $this->keys() ? $env->dictionary()->only($this->keys()) : $env->dictionary();

        $items = $vars->map(function ($value, $key) {
            return (object) compact('key', 'value');
        });

        $args      = $this->args->toArray(); // var required - passed by reference
        $formatter = new Formatter($args, $this->fields());
        $formatter->display_items($items->all());
    }

    /**
     * Get the keys passed in the arguments.
     *
     * Multiple keys can be passed in the cli arguments as a comma-separated list.
     * This converts those to an array, if passed.
     *
     * @return array
     */
    protected function keys()
    {
        return is_string($this->args->keys)
            ? explode(',', $this->args->keys)
            : $this->args->keys;
    }

    /**
     * Get the fields to display as passed in the arguments.
     *
     * Multiple keys can be passed in the cli arguments as a comma-separated list.
     * This converts those to an array, if passed.
     *
     * @return array
     */
    protected function fields()
    {
        return is_string($this->args->fields)
            ? explode(',', $this->args->fields)
            : $this->args->fields;
    }

    /**
     * Iterate over each line and prompt for a new value
     *
     * @param File $env
     *
     * @return int
     */
    protected function prompt_all(File $env)
    {
        $env->dictionary()->each(function ($value, $key) use ($env) {
            $env->set($key, $this->prompt($key, $value));
        });

        $env->save();
    }
}
