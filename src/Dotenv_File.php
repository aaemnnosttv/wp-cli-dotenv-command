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

    /**
     * Dotenv_File constructor.
     *
     * @param $filepath
     */
    public function __construct( $filepath )
    {
        $this->filepath = $filepath;
        $this->filename = basename($filepath);
    }

    /**
     * @param $filepath
     *
     * @return static
     */
    public static function create( $filepath )
    {
        $dotenv = new static( $filepath );

        if ( ! $dotenv->exists() ) {
            touch( $filepath );
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
    public function add_line( $value )
    {
        array_push($this->lines, (string) $value);
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function get_pattern_for_key( $key )
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
        return count( $this->lines );
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
    public function get( $key )
    {
        foreach ( $this->lines as &$line )
        {
            if ( $this->is_key_match( $key, $line ) )
            {
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
    public function set( $key, $value )
    {
        $set = $replaced = false;
        $new_line = format_line($key, $value);

        foreach ( $this->lines as &$line )
        {
            if ( $this->is_key_match( $key, $line ) )
            {
                $line = $new_line;
                $set = $replaced = true;
            }
        }

        // if it wasn't replaced, append it
        if ( ! $replaced ) {
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
    public function remove( $key )
    {
        $removed = 0;

        $this->filter(function($line) use ($key, &$removed)
        {
            if ( $this->is_key_match($key, $line) ) {
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
     * @return $this
     */
    public function map( callable $callback )
    {
        $this->lines = array_map($callback, $this->lines);

        return $this;
    }

    /**
     * @param callable $callback
     *
     * @return $this
     */
    public function filter( callable $callback )
    {
        $this->lines = array_filter($this->lines, $callback);

        return $this;
    }

    /**
     * @param $line
     *
     * @return bool|string
     */
    protected function parse_line_value( $line )
    {
        // key = value
        // key=value
        // key = "value"
        // key='value' << our format

        if ( ! strpos($line, '=') ) return false;

        // break the line at the = into 2 pieces (key, value)
        $pieces = explode('=', $line, 2);
        // clean off any extra whitespace or wrapping quotes
        $value = trim( array_pop($pieces), '\'""' );

        return $value;
    }

    /**
     * @param $key
     * @param $line
     *
     * @return int
     */
    protected function is_key_match( $key, $line )
    {
        return preg_match( $this->get_pattern_for_key( $key ), $line );
    }

    /**
     * Whether or not the file defines the given key
     *
     * @param $key
     *
     * @return bool
     */
    public function has_key( $key )
    {
        foreach ( $this->lines as $line )
        {
            if ( $this->is_key_match($key, $line) )
                return true;
        }

        return false;
    }

    /**
     * @param $line
     *
     * @return bool
     */
    public function get_key_for_line( $line )
    {
        $pieces = explode( '=', $line, 2 );
        $pieces = array_map( 'trim', $pieces );

        return array_shift( $pieces );
    }

    /**
     * @param $line
     *
     * @return array
     */
    public function get_pair_for_line( $line )
    {
        $pieces = explode( '=', $line, 2 );
        $pieces = array_map( 'trim', $pieces );

        $key   = array_shift( $pieces );
        $value = array_shift( $pieces );

        if ( 0 === strpos( $value, '\'' ) ) {
            $value = trim( $value, '\'' );
        }
        elseif ( 0 === strpos( $value, '"' ) ) {
            $value = trim( $value, '"' );
        }

        return compact('key','value');
    }

    public function get_pairs()
    {
        $pairs = [ ];

        $this->map(function($line) use (&$pairs)
        {
            $pair = $this->get_pair_for_line($line);

            if ( strlen($pair['key']) ) {
                $pairs[ $pair['key'] ] = $pair['value'];
            }
        });

        return $pairs;
    }

}