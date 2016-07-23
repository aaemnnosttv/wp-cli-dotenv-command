<?php

namespace WP_CLI_Dotenv\Dotenv;

use InvalidArgumentException;
use Illuminate\Support\Collection;

/**
 * Class File
 * @package WP_CLI_Dotenv_Command
 */
class File
{
    /**
     * Absolute path to the file
     * @var string
     */
    protected $path;

    /**
     * Lines collection
     * @var FileLines
     */
    protected $lines;

    /**
     * File constructor.
     *
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Get a new instance, and ensure the file is readable.
     *
     * @param $path
     *
     * @throws InvalidArgumentException
     *
     * @return static
     */
    public static function at($path)
    {
        $file = new static($path);

        if (! is_readable($path)) {
            throw new InvalidArgumentException("File not readable at $path");
        }

        return $file;
    }

    /**
     * Get a new instance, and ensure the file is writable.
     *
     * @param $path
     *
     * @throws InvalidArgumentException
     *
     * @return static
     */
    public static function writable($path)
    {
        $file = static::at($path);

        if (! is_writable($path)) {
            throw new InvalidArgumentException("File not writable at $path");
        }

        return $file;
    }


    /**
     * Create a new instance, including the file and parent directories.
     *
     * @param $path
     *
     * @return static
     */
    public static function create($path)
    {
        $file = new static($path);

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        if (! $file->exists()) {
            touch($path);
        }

        return $file;
    }

    /**
     * Whether the file exists and is readable
     *
     * @return bool
     */
    public function is_readable()
    {
        return is_readable($this->path);
    }

    /**
     * @return bool
     */
    public function is_writable()
    {
        return is_writable($this->path);
    }

    /**
     * @return $this
     */
    public function load()
    {
        $this->lines = FileLines::load($this->path);

        return $this;
    }

    /**
     * Get the full path to the file.
     *
     * @return string
     */
    public function path()
    {
        return $this->path;
    }

    public function save()
    {
        $contents = $this->lines->implode(PHP_EOL) . PHP_EOL;

        return file_put_contents($this->path, $contents);
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->path);
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
        return $this->lines->first(function($index, LineInterface $line) use ($key) {
            return $line->key() == $key;
        }, new Line)->value();
    }

    /**
     * @param        $key
     * @param        $value
     * @param string $quote
     */
    public function set($key, $value, $quote = '')
    {
        $index = $this->lines->search(function (LineInterface $line) use ($key) {
            return $line->key() == $key;
        });

        $line = new KeyValue($key, $value, $quote);

        if ($index > -1) {
            $this->lines->set($index, $line);
            return;
        }

        $this->lines->add($line);
    }

    /**
     * @param $key
     *
     * @return int Lines removed
     */
    public function remove($key)
    {
        $linesBefore = $this->lines->count();

        $this->lines = $this->lines->reject(function (LineInterface $line) use ($key) {
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
        return (bool) $this->lines->first(function (LineInterface $line) use ($key) {
            return $line->key() == $key;
        });
    }

    /**
     * Get the lines as key => value.
     *
     * @return Collection
     */
    public function dictionary()
    {
        return $this->lines->dictionary();
    }

}
