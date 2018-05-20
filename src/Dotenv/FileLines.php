<?php

namespace WP_CLI_Dotenv\Dotenv;

class FileLines extends Collection
{
    /**
     * Create a new instance from the given file path.
     *
     * @param $filePath
     *
     * @return static
     */
    public static function load($filePath)
    {
        return static::fromArray(file($filePath, FILE_IGNORE_NEW_LINES));
    }

    /**
     * Return a new instance using an array of raw lines.
     *
     * @param array $lines
     *
     * @return mixed
     */
    public static function fromArray(array $lines)
    {
        return (new static($lines))->parse();
    }

    /**
     * Parse the raw lines into their respective classes.
     *
     * @return static
     */
    public function parse()
    {
        return $this->map(function ($lineText) {
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
            $this->put($index, $line);
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

    /**
     * Get a subset of lines where the key matches at least one of the given glob-style patterns.
     *
     * @param string|array|Collection $patterns
     *
     * @return static
     */
    public function whereKeysLike($patterns)
    {
        if (! $patterns instanceof Collection) {
            $patterns = new Collection((array) $patterns);
        }

        /* @var Collection $patterns */

        if ($patterns->filter()->isEmpty()) {
            return new static($this->all());
        }

        /**
         * Return a subset of pairs that match any of the given patterns.
         */
        return $this->pairs()->filter(function (LineInterface $line) use ($patterns) {
            return $patterns->contains(function ($index, $pattern) use ($line) {
                return fnmatch($pattern, $line->key());
            });
        });
    }
}
