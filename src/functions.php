<?php namespace WP_CLI_Dotenv_Command;

use WP_CLI;

/**
 * @param $key
 * @param $value
 *
 * @return string
 */
function format_line( $key, $value )
{
    return sprintf(Dotenv_File::LINE_FORMAT, $key, $value);
}

/**
 * Get the absolute path for the .env file
 *
 * @param $assoc_args
 *
 * @return string
 */
function get_filepath( $assoc_args )
{
    $file = \WP_CLI\Utils\get_flag_value( $assoc_args, 'file', '.env' );

    // if absolute path was passed
    if ( \WP_CLI\Utils\is_path_absolute( $file ) ) {
        return realpath( $file );
    }

    // if relative path, or just a file name was passed
    return realpath( getcwd() . '/' . $file );
}

/**
 * Get a new Dotenv_File instance from CLI args
 *
 * @param $args
 *
 * @return Dotenv_File
 */
function get_dotenv( $args )
{
    $filepath = get_filepath( $args );

    return new Dotenv_File( $filepath );
}

/**
 * Load the .env file, while ensuring read permissions
 * or die trying!
 *
 * @param $args
 *
 * @return Dotenv_File
 */
function get_dotenv_for_read_or_fail( $args )
{
    $dotenv = get_dotenv($args);

    if ( ! $dotenv->exists() ) {
        WP_CLI::error('File does not exist: ' . $dotenv->get_filepath());
        exit;
    }

    if ( $dotenv->is_readable() ) {
        return $dotenv->load();
    }

    WP_CLI::error($dotenv->get_filepath() . ' is not readable! Check your file permissions.');
    exit;
}

/**
 * Load the .env file, while ensuring write permissions
 * or die trying!
 *
 * @param $args
 *
 * @return Dotenv_File
 */
function get_dotenv_for_write_or_fail( $args )
{
    $dotenv = get_dotenv_for_read_or_fail($args);

    if ( $dotenv->is_writable() ) return $dotenv; // already loaded

    WP_CLI::error($dotenv->get_filepath() . ' is not writable! Check your file permissions.');
    exit;
}

/**
 * CLI input prompt
 *
 * @param $question
 * @param $default
 *
 * @return bool
 */
function prompt( $question, $default )
{
    try {
        $response = \cli\prompt( $question, $default );
    } catch( \Exception $e ) {
        WP_CLI::line();
        return false;
    }

    return $response;
}

/**
 * Keys defined by WP Salt Generator
 * @return array
 */
function salt_keys()
{
    return [
        'AUTH_KEY',
        'SECURE_AUTH_KEY',
        'LOGGED_IN_KEY',
        'NONCE_KEY',
        'AUTH_SALT',
        'SECURE_AUTH_SALT',
        'LOGGED_IN_SALT',
        'NONCE_SALT'
    ];
}