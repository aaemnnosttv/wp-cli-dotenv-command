<?php namespace WP_CLI_Dotenv_Command;

/**
 * Class Dotenv_File
 * @package WP_CLI_Dotenv_Command
 */
class Dotenv_File
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
     * @var array
     */
    protected $lines = [];

    /**
     * Single line format
     */
    const LINE_FORMAT = '%s=\'%s\'';

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
     * @param $value
     */
    public function add_line($value)
    {
        array_push($this->lines, (string)$value);
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
        $this->lines = file($this->filepath, FILE_IGNORE_NEW_LINES);

        return $this;
    }

    /**
     * @return int
     */
    public function save()
    {
        $contents = join(PHP_EOL, $this->lines) . PHP_EOL;

        return file_put_contents($this->filepath, $contents);
    }

    /**
     * @return int
     */
    public function size()
    {
        return count($this->lines);
    }

    /**
     * Get the value for a key
     *
     * Ex using our format:
     * KEY='VALUE'
     *
     * @param $key
     *
     * @return bool|null|string     string value,
     *                              false if line does not have `=`, or null if
     *                              null if no match was found
     */
    public function get($key)
    {
        foreach ($this->lines as &$line) {
            if ($this->is_key_match($key, $line)) {
                return $this->parse_line_value($line);
            }
        }

        // if we got here, the key wasn't matched
        return null;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function set($key, $value)
    {
        $set      = $replaced = false;
        $new_line = format_line($key, $value);

        foreach ($this->lines as &$line) {
            if ($this->is_key_match($key, $line)) {
                $line = $new_line;
                $set  = $replaced = true;
            }
        }

        // if it wasn't replaced, append it
        if ( ! $replaced) {
            $this->add_line($new_line);
            $set = true;
        }

        return $set;
    }

    /**
     * @param $key
     *
     * @return int Lines removed
     */
    public function remove($key)
    {
        $removed = 0;

        $this->lines = $this->filter(function ($line) use ($key, &$removed) {
            if ($this->is_key_match($key, $line)) {
                $removed++;

                return false;
            }

            return true;
        });

        return $removed;
    }

    /**
     * @param callable $callback
     *
     * @return array
     */
    public function map(callable $callback)
    {
        return array_map($callback, $this->lines);
    }

    /**
     * @param callable $callback
     *
     * @return $this
     */
    public function transform(callable $callback)
    {
        $this->lines = $this->map($callback);

        return $this;
    }

    /**
     * @param callable $callback
     *
     * @return array
     */
    public function filter($callback = null)
    {
        return array_filter($this->lines, $callback);
    }

    /**
     * @param $line
     *
     * @return bool|string
     */
    protected function parse_line_value($line)
    {
        // key = value
        // key=value
        // key = "value"
        // key='value' << our format

        if ( ! strpos($line, '=')) {
            return false;
        }

        // break the line at the = into 2 pieces (key, value)
        $pieces = explode('=', $line, 2);
        // clean off any extra whitespace or wrapping quotes
        $value = trim(array_pop($pieces), '\'""');

        return $value;
    }

    /**
     * @param $key
     * @param $line
     *
     * @return int
     */
    protected function is_key_match($key, $line)
    {
        return preg_match($this->get_pattern_for_key($key), $line);
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
        foreach ($this->lines as $line) {
            if ($this->is_key_match($key, $line)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $line
     *
     * @return bool
     */
    public function get_key_for_line($line)
    {
        $pieces = explode('=', $line, 2);
        $pieces = array_map('trim', $pieces);

        return array_shift($pieces);
    }

    /**
     * @param $line
     *
     * @return bool|array
     */
    public function get_pair_for_line($line)
    {
        $pieces = explode('=', $line, 2);
        $pieces = array_map('trim', $pieces);

        if (2 !== count($pieces)) {
            return false;
        }

        list($key, $value) = $pieces;

        if (is_null($key) || is_null($value)) {
            return false;
        }

        $value = $this->clean_quotes($value);

        return compact('key', 'value');
    }

    /**
     * Trim surrounding quotes from a string
     *
     * @param $string
     *
     * @return string
     */
    protected function clean_quotes($string)
    {
        $first_char = mb_substr((string) $string,  0);
        $last_char  = mb_substr((string) $string, -1);

        /**
         * Test the first and last character for quote type
         */
        if (1 === count(array_unique([$first_char, $last_char, self::QUOTE_SINGLE]))) {
            return trim($string, self::QUOTE_SINGLE);
        }

        if (1 === count(array_unique([$first_char, $last_char, self::QUOTE_DOUBLE]))) {
            return trim($string, self::QUOTE_DOUBLE);
        }

        return $string;
    }

    /**
     * @return array
     */
    public function get_pairs()
    {
        $pairs = [];

        foreach ($this->lines as $line) {
            if ($pair = $this->get_pair_for_line($line)) {
                $pairs[ $pair['key'] ] = $pair['value'];
            }
        }

        return $pairs;
    }

}