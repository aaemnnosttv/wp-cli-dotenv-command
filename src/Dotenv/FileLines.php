<?php

namespace WP_CLI_Dotenv\Dotenv;

use Illuminate\Support\Collection;

class FileLines extends Collection
{
    /**
     * Create a new collection of file lines from the file at given path.
     *
     * @param $filePath
     *
     * @return static
     */
    public static function load($filePath)
    {
        return static::make(file($filePath, FILE_IGNORE_NEW_LINES))
            ->map(function ($lineText) {
                return Line::parse_raw($lineText);
            });
    }

    /**
     * Add a new line to the collection.
     *
     * @param LineInterface $line
     *
     * @return $this
     */
    public function add(LineInterface $line)
    {
        return $this->push($line);
    }

    /**
     * Get the value of a defined variable.
     *
     * @param mixed $key
     * @param null  $default
     *
     * @return mixed
     */
    public function getDefinition($key, $default = null)
    {
        return $this->toDictionary()->get($key, $default);
    }

    /**
     * Put a new line at a given key/index.
     *
     * @param mixed         $index
     * @param LineInterface $line
     *
     * @return $this
     */
    public function set($index, LineInterface $line)
    {
        parent::put($index, $line);

        return $this;
    }

    /**
     * Update the line by key, or add it if there is no existing line for the same key.
     *
     * @param LineInterface $line
     */
    public function updateOrAdd(LineInterface $line)
    {
        $key = $line->key();

        $index = $this->search(function (LineInterface $line) use ($key) {
            return $line->key() == $key;
        });

        if ($index > -1) {
            $this->set($index, $line);
        } else {
            $this->add($line);
        }
    }

    /**
     * Check if the collection has a definition for the given variable name.
     *
     * @param $varName
     *
     * @return bool
     */
    public function hasDefinition($varName)
    {
        return $this->contains(function ($index, LineInterface $line) use ($varName) {
            return $line->key() == $varName;
        });
    }

    /**
     * Remove the definition by the variable name.
     *
     * @param string $varName
     *
     * @return $this
     */
    public function removeDefinition($varName)
    {
        $this->items = $this->reject(function (LineInterface $line) use ($varName) {
            return $line->key() == $varName;
        })->all();

        return $this;
    }

    /**
     * Convert the lines back to a single string.
     *
     * @return string
     */
    public function toString()
    {
        return $this->map(function (LineInterface $line) {
            return $line->toString();
        })->implode(PHP_EOL);
    }

    /**
     * Return a new collection of only key value line pairs
     *
     * @return static
     */
    public function pairs()
    {
        return $this->filter(function (LineInterface $line) {
            return $line instanceof KeyValue;
        });
    }

    /**
     * Get the lines as key => value.
     *
     * @return Collection
     */
    public function toDictionary()
    {
        return $this->pairs()->reduce(function (Collection $pairs, LineInterface $line) {
            $pairs[ $line->key() ] = $line->value();

            return $pairs;
        }, new Collection);
    }
}
