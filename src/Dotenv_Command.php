<?php namespace WP_CLI_Dotenv_Command;

use WP_CLI;
use WP_CLI\Formatter;
use WP_CLI_Command;

/**
 * Manage a .env file
 * @package WP_CLI_Dotenv_Command
 */
class Dotenv_Command extends WP_CLI_Command
{

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
     * : Set new values from the template interactively. Leave blank for no change.
     *
     * @synopsis [--file=<path-to-dotenv>] [--with-salts] [--template=<template-name>] [--interactive]
     *
     * @when before_wp_load
     */
    public function init( $_, $assoc_args )
    {
        $filepath = get_filepath($assoc_args);

        if ( file_exists( $filepath ) ) {
            WP_CLI::error("Environment file already exists at: $filepath");
            return;
        }

        $dotenv = Dotenv_File::create( $filepath );

        if ( ! $dotenv->exists() ) {
            WP_CLI::error('Failed to create environment file at: ' . $dotenv->get_filepath());
            return;
        }

        if ( $template = \WP_CLI\Utils\get_flag_value( $assoc_args, 'template' ) ) {
            $this->init_from_template($dotenv, $template, $assoc_args);
        }

        if ( \WP_CLI\Utils\get_flag_value( $assoc_args, 'with-salts' ) ) {
            WP_CLI::run_command( [ 'dotenv', 'salts', 'generate' ], [ 'file' => $dotenv ] );
        }

        WP_CLI::success("$filepath created.");
    }

    /**
     * @param $dotenv
     * @param $template
     * @param $assoc_args
     */
    protected function init_from_template( Dotenv_File &$dotenv, $template, $assoc_args )
    {
        $template_path = get_filepath(['file' => $template]);

        if ( ! file_exists($template_path) ) {
            WP_CLI::error("Template file does not exist at: $template_path");
            return;
        }
        if ( ! is_readable($template_path) ) {
            WP_CLI::error( "Template file is not readable at: $template_path" );
            return;
        }
        if ( ! $dotenv->is_writable() ) {
            WP_CLI::error('Environment file is not readable at: ' . $dotenv->get_filepath());
            return;
        }

        WP_CLI::line("Initializing from template: $template_path");

        copy( $template_path, $dotenv->get_filepath() );

        // we can't use WP-CLI --prompt because we're working off the template, not the synopsis
        if ( ! $interactive = \WP_CLI\Utils\get_flag_value( $assoc_args, 'interactive' ) )
            return;

        WP_CLI::line('Interactive init');
        WP_CLI::line('Specify a new value for each key, or leave blank for no change.');

        // iterate over each line and prompt for a new value
        $dotenv->map(function( $line ) use ( $dotenv )
        {
            $pair = $dotenv->get_pair_for_line($line);

            if ( ! $pair['key'] ) return $line;

            $user_value = \cli\prompt( $pair[ 'key' ], $pair[ 'value' ] );

            if ( ! strlen($user_value) ) return $line;

            return format_line( $pair[ 'key' ], $user_value );
        });

        $dotenv->save();
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
     * @when before_wp_load
     */
    public function set( $_, $assoc_args )
    {
        list( $key, $value ) = $_;

        $dotenv = get_dotenv_for_write_or_fail($assoc_args);
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
     * @when before_wp_load
     */
    public function get( $_, $assoc_args )
    {
        list( $key ) = $_;

        $dotenv = get_dotenv_for_read_or_fail( $assoc_args );
        $value = $dotenv->get( $key );

        if ( $value || ! in_array( $value, [ false, null ], true ) )
        {
            WP_CLI::line( $value );
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
     * @when before_wp_load
     */
    public function delete( $_, $assoc_args )
    {
        $dotenv = get_dotenv_for_write_or_fail( $assoc_args );

        foreach ( $_ as $key )
        {
            if ( $result = $dotenv->remove($key) ) {
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
     * @when before_wp_load
     */
    public function _list( $_, $assoc_args )
    {
        $dotenv = get_dotenv_for_read_or_fail( $assoc_args );
        $keys   = \WP_CLI\Utils\get_flag_value( $assoc_args, 'keys', [ ] );
        $keys   = explode( ',', $keys );
        $items  = [ ];

        foreach ( $dotenv->get_pairs() as $key => $value )
        {
            // Skip if not requested
            if ( ! empty( $keys ) && ! in_array( $key, $keys ) ) {
                continue;
            }

            $items[ ] = (object) compact('key', 'value');
        }

        $fields = \WP_CLI\Utils\get_flag_value( $assoc_args, 'fields', ['key','value'] );
        $fields = is_string($fields) ? explode( ',', $fields ) : $fields;

        $formatter = new Formatter( $assoc_args, $fields );
        $formatter->display_items( $items );
    }

}
