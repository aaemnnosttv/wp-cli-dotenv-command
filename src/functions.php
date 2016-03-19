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
    if (strpos($value, ' ') !== false) {
        $value = "'$value'";
    }
    return sprintf(Dotenv_File::LINE_FORMAT, $key, $value);
}

/**
 * Get the absolute path for the .env file
 *
 * @param $file
 *
 * @return string
 */
function get_filepath($file)
{
    if (file_exists($file)) {
        return $file;
    }

    $dirname  = dirname($file);
    $filename = basename($file);
    $relpath  = $dirname ? "/$dirname" : '';
    $path     = getcwd() . "$relpath/$filename";

    /**
     * realpath will return false if path does not exist
     */
    return realpath($path) ?: $path;
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
        return $dotenv->load();
    } catch (\Exception $e) {
        WP_CLI::error($e->getMessage());
        exit;
    }
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
