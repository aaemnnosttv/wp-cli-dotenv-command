<?php

namespace WP_CLI_Dotenv_Command;

use WP_CLI;

/**
 * @param $key
 * @param $value
 *
 * @return string
 */
function format_line($key, $value)
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
function get_filepath($assoc_args)
{
    $file = \WP_CLI\Utils\get_flag_value($assoc_args, 'file', '.env');

    if ($file instanceof Dotenv_File) {
        return $file->get_filepath();
    }

    // if relative path, or just a file name was passed
    $dirname  = dirname($file);
    $filename = basename($file);
    $relpath  = $dirname ? "/$dirname" : '';
    $path     = realpath(getcwd() . $relpath);
    $path .= "/$filename";

    return $path;
}

/**
 * Load the environment file, while ensuring read permissions
 * or die trying!
 *
 * @param $args
 *
 * @return Dotenv_File
 */
function get_dotenv_for_read_or_fail($args)
{
    $filepath = get_filepath($args);

    try {
        $dotenv = Dotenv_File::at($filepath);
        $dotenv->load();
    } catch (\Exception $e) {
        WP_CLI::error($e->getMessage());
        exit;
    }

    return $dotenv;
}

/**
 * Load the environment file, while ensuring write permissions
 * or die trying!
 *
 * @param $args
 *
 * @return Dotenv_File
 */
function get_dotenv_for_write_or_fail($args)
{
    $filepath = get_filepath($args);

    try {
        $dotenv = Dotenv_File::writable($filepath);
        $dotenv->load();
    } catch (\Exception $e) {
        WP_CLI::error($e->getMessage());
        exit;
    }

    return $dotenv;
}

/**
 * CLI input prompt
 *
 * @param $question
 * @param $default
 *
 * @return bool
 */
function prompt($question, $default)
{
    try {
        $response = \cli\prompt($question, $default);
    } catch (\Exception $e) {
        WP_CLI::error($e->getMessage());

        return false;
    }

    return $response;
}
