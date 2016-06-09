<?php

namespace WP_CLI_Dotenv\Dotenv;

use Illuminate\Support\Collection;

/**
 * Class File
 * @package WP_CLI_Dotenv_Command
 */
class File
{
    /**
     * Absolute path to file
     * @var string
     */
    protected $filepath;
    /**
     * File name
     * @var string
     */
    protected $filename;
    /**
     * File lines
     * @var FileLines
     */
    protected $lines;

    /**
     * Single line format
     */
    const LINE_FORMAT = '%s=%s';

    /**
     * Pattern to match a var definition
     */
    const PATTERN_KEY_CAPTURE_FORMAT = '/^%s(\s+)?=/';

    const QUOTE_SINGLE = '\'';

    const QUOTE_DOUBLE = '"';

    /**
     * Dotenv_File constructor.
     *
     * @param $filepath
     */
    public function __construct($filepath)
    {
        $this->filepath = $filepath;
        $this->filename = basename($filepath);
    }

    /**
     * @param $filepath
     *
     * @return static
     */
    public static function at($filepath)
    {
        $dotenv = new static($filepath);

        if (! $dotenv->is_readable()) {
            throw new \RuntimeException("File not readable at $filepath");
        }

        return $dotenv;
    }

    /**
     * @param $filepath
     *
     * @return Dotenv_File
     */
    public static function writable($filepath)
    {
        $dotenv = static::at($filepath);

        if (! $dotenv->is_writable()) {
            throw new \RuntimeException("File not writable at $filepath");
        }

        return $dotenv;
    }


    /**
     * @param $filepath
     *
     * @return static
     */
    public static function create($filepath)
    {
        $dotenv = new static($filepath);

        if ( ! is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        if ( ! $dotenv->exists()) {
            touch($filepath);
        }

        return $dotenv;
    }

    /**
     * @return string
     */
    public function get_filepath()
    {
        return $this->filepath;
    }

    /**
     * @return string
     */
    public function get_filename()
    {
        return $this->filename;
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->filepath);
    }

    /**
     * Whether the file exists and is readable
     *
     * @return bool
     */
    public function is_readable()
    {
        return is_readable($this->filepath);
    }

    /**
     * @return bool
     */
    public function is_writable()
    {
        return is_writable($this->filepath);
    }

    /**
     * @param string $text
     */
    public function add_line($text)
    {
        $this->lines->push(new Line($text));
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function get_pattern_for_key($key)
    {
        $preg_key = preg_quote(trim($key), '/');

        return sprintf(static::PATTERN_KEY_CAPTURE_FORMAT, $preg_key);
    }

    /**
     * @return $this
     */
    public function load()
    {
        $this->lines = FileLines::load($this->filepath);

        return $this;
    }

    /**
     * @return int
     */
    public function save()
    {
        $contents = $this->lines->implode(PHP_EOL) . PHP_EOL;

        return file_put_contents($this->filepath, $contents);
    }

    /**
     * @return int
     */
    public function size()
    {
        return $this->lines->count();
    }

    /**
     * Get the value for a key
     *
     * Ex using our format:
     * KEY='VALUE'
     *
     * @param $key
     *
     * @return null|string          string value,
     *                              null if no match was found
     */
    public function get($key)
    {
        return $this->lines->first(function($index, Line $line) use ($key) {
            return $line->key() == $key;
        }, new Line)->value();
    }

    /**
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function set($key, $value)
    {
        $index = $this->lines->search(function (Line $line) use ($key) {
            return $line->key() == $key;
        });

        if ($index > -1) {
            $this->lines->put($index, Line::fromPair($key, $value));
            return;
        }

        $this->lines->push(Line::fromPair($key, $value));
    }

    /**
     * @param $key
     *
     * @return int Lines removed
     */
    public function remove($key)
    {
        $linesBefore = $this->lines->count();

        $this->lines = $this->lines->reject(function (Line $line) use ($key) {
            return $line->key() == $key;
        });

        return $linesBefore - $this->lines->count();
    }

    /**
     * Whether or not the file defines the given key
     *
     * @param $key
     *
     * @return bool
     */
    public function has_key($key)
    {
        return (bool) $this->lines->first(function (Line $line) use ($key) {
            return $line->key() == $key;
        });
    }

    /**
     * @return array
     */
    public function get_pairs()
    {
        return $this->lines->pairs();
    }

}
