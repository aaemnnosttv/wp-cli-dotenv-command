<?php

use WP_CLI_Dotenv\Dotenv\File;

/**
 * @param $key
 * @param $value
 *
 * @return string
 * 
 * @deprecated 
 */
function format_line($key, $value)
{
    if (strpos($value, ' ') !== false) {
        $value = "'$value'";
    }
    return sprintf(File::LINE_FORMAT, $key, $value);
}

/**
 * Get the absolute path for the .env file
 *
 * @param $file
 *
 * @return string
 * 
 * @deprecated
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
 * @return File
 * 
 * @deprecated
 */
function get_dotenv_for_read_or_fail($args)
{
    $filepath = get_filepath($args);

    try {
        $dotenv = File::at($filepath);
        return $dotenv->load();
    } catch (\Exception $e) {
        WP_CLI::error($e->getMessage());
    }
}

/**
 * Load the environment file, while ensuring write permissions
 * or die trying!
 *
 * @param $args
 *
 * @return File
 */
function get_dotenv_for_write_or_fail($args)
{
    $filepath = get_filepath($args);

    try {
        $dotenv = File::writable($filepath);
        $dotenv->load();
    } catch (\Exception $e) {
        WP_CLI::error($e->getMessage());
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
        return \cli\prompt($question, $default);
    } catch (\Exception $e) {
        WP_CLI::error($e->getMessage());
    }
}
