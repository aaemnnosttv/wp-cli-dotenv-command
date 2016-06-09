<?php

namespace WP_CLI_Dotenv\Dotenv;

use Illuminate\Support\Collection;

class FileLines extends Collection
{
    /**
     * Create a new collection of file lines from the file at given path
     * @param  [type] $filepath [description]
     * @return [type]           [description]
     */
    public static function load($filepath)
    {
        return (new static(file($filepath, FILE_IGNORE_NEW_LINES)))
            ->map(function ($lineText) {
                return new Line($lineText);
            });
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->map(function ($line) {
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
        return $this->filter(function ($line) {
            return $line->isPair();
        });
    }
}
