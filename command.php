<?php namespace WP_CLI_Dotenv_Command;

/*
Copyright (C) 2015  Evan Mattson

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

use WP_CLI;
use WP_CLI_Command; // duh

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
    const PATTERN_KEY_FORMAT = '/^%s(\s+)?=/';

    /**
     *
     */
    const PATTERN_VALUE_FORMAT = '/^%s(\s+)?=/';

    /**
     * Dotenv_File constructor.
     *
     * @param $filepath
     */
    public function __construct( $filepath )
    {
        if ( is_dir($filepath) ) {
            $filepath = rtrim($filepath, '\\/') . '/.env';
        }

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
        $dotenv = new static($filepath);

        if ( ! $dotenv->exists() ) {
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
        return sprintf(static::PATTERN_KEY_FORMAT, $preg_key);
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
}


/**
 * Manage a .env file
 * @package WP_CLI_Dotenv_Command
 */
class Dotenv_Command extends WP_CLI_Command
{
    /**
     * @var
     */
    protected $dotenv;

    /**
     * @var array
     */
    protected $reserved_keys = [
        'file'
    ];

    /**
     * Initialize the .env file
     *
     * [--file=<path-to-dotenv>]
     * : Path to .env. Defaults to current directory.
     *
     * @synopsis [--file=<path-to-dotenv>]
     *
     * @when before_wp_load
     */
    public function init( $_, $assoc_args )
    {
        $filepath = get_filepath($assoc_args);

        if ( file_exists( $filepath ) ) {
            WP_CLI::error('.env already exists!');
            return;
        }

        $dotenv = Dotenv_File::create($filepath);

        if ( $dotenv->exists() ) {
            WP_CLI::success("$filepath created successfully!");
        }
    }

    /**
     * Generate a .env file.
     *
     * ## OPTIONS
     *
     * [--file=<path-to-dotenv>]
     * : Path to .env. Defaults to current directory.
     *
     * [--skip-salts]
     * : If set, keys and salts won't be generated.
     *
     * ## EXAMPLES
     *
     *     # Standard .env file
     *     wp dotenv config --DB_NAME=testing --DB_USER=wp --DB_PASSWORD=securepswd
     *
     * @when before_wp_load
     */
    public function config( $_, $assoc_args )
    {
        $filepath = get_filepath($assoc_args);

        if ( file_exists($filepath) ) {
            WP_CLI::error('.env already exists!');
            return;
        }

        Dotenv_File::create($filepath);
        $dotenv = get_dotenv_for_write_or_fail($assoc_args);

        // handle any args passed as vars to set
        foreach ( $assoc_args as $assoc_arg => $value )
        {
            if ( in_array($assoc_arg, $this->reserved_keys) ) {
                continue;
            }

            $dotenv->set($assoc_arg, $value);
        }

        // add salts
        if ( ! \WP_CLI\Utils\get_flag_value( $assoc_args, 'skip-salts' ) )
        {
            foreach( Salts::fetch_array() as $salt_key => $salt_val ) {
                $dotenv->set($salt_key, $salt_val);
            }
        }

        $dotenv->save();

        WP_CLI::success( sprintf('Wrote %d lines.', $dotenv->size()) );
    }

    /**
     * Set an environment value in .env
     * @synopsis <key> <value>
     *
     * @when before_wp_load
     */
    public function set( $_, $assoc_args )
    {
        $key = $_[0];
        $value = $_[1];

        $dotenv = get_dotenv_for_write_or_fail($assoc_args);

        if ( $dotenv->set($key, $value) ) {
            WP_CLI::success("'$key' set successfully!");
            $dotenv->save();
            return;
        }

        WP_CLI::error('There was a problem trying to set that. Sorry!');
    }

    /**
     * Get an environment value from .env
     *
     * @synopsis <key>
     *
     * @when before_wp_load
     */
    public function get( $_, $assoc_args )
    {
        $key = $_[0];

        $dotenv = get_dotenv_for_read_or_fail($assoc_args);
        $value = $dotenv->get($key);

        if ( $value || ! in_array($value, [false, null], true) ) {
            WP_CLI::line($value);
            return;
        }

        if ( false === $value ) {
            WP_CLI::error('Invalid line format for key');
        }
        else {
            WP_CLI::error("Key '$key' not found.");
        }

    }

}
WP_CLI::add_command( 'dotenv', __NAMESPACE__ . '\\Dotenv_Command' );


/**
 * Class Salts
 * @package WP_CLI_Dotenv_Command
 */
class Salts
{
    /**
     *
     */
    const GENERATOR_URL = 'https://api.wordpress.org/secret-key/1.1/salt/';

    /**
     *
     */
    const PATTERN_CAPTURE = '#\'([^\']+)\'#';

    /**
     * @return array|void
     */
    public static function fetch_array()
    {
        // read in each line as an array
        $response = file(static::GENERATOR_URL, FILE_IGNORE_NEW_LINES);

        if ( ! is_array( $response ) ) {
            WP_CLI::error('There was a problem fetching the salts from the WordPress generator service.');
            return;
        }

        return (array) static::parse_php_to_array( $response );
    }

    /**
     * Parse the php generated by the WordPress.org salts generator to an array of key => value pairs
     *
     * @param $response
     *
     * @return array
     */
    public static function parse_php_to_array( array $response )
    {
        $salts = [ ];

        foreach ( $response as $line )
        {
            // capture everything between single quotes
            preg_match_all( self::PATTERN_CAPTURE, $line, $matches );

            // 0 - complete match
            // 1 - captures
            if ( ! isset( $matches[ 1 ] ) ) {
                continue;
            }

            $captures = $matches[ 1 ];

            $constant_name  = array_shift( $captures );
            $constant_value = array_shift( $captures );

            if ( $constant_name ) {
                $salts[ $constant_name ] = $constant_value;
            }

            unset( $matches );
        }

        return $salts;
    }
}


/**
 * Manage WordPress salts in .env format
 * @package WP_CLI_Dotenv_Command
 */
class Dotenv_Salts_Command extends WP_CLI_Command
{

    /**
     * Fetch some fresh salts and output them in dotenv format
     *
     * @when before_wp_load
     */
    function generate()
    {
        foreach( Salts::fetch_array() as $key => $value ) {
            WP_CLI::line( format_line($key, $value) );
        }
    }

    /**
     * Regenerate salts for a .env file
     *
     * [--file=<path-to-dotenv>]
     * : Path to .env. Defaults to current directory.
     *
     * @synopsis [--file=<path-to-dotenv>]
     *
     * @when before_wp_load
     */
    function regenerate( $_, $assoc_args )
    {
        $dotenv = get_dotenv_for_write_or_fail($assoc_args);
        $salts = Salts::fetch_array();

        if ( ! $salts ) return;

        foreach ( $salts as $key => $value )
        {
            $dotenv->set($key, $value);
        }

        $dotenv->save();

        WP_CLI::success('Salts regenerated.');
    }



}
WP_CLI::add_command( 'dotenv salts', __NAMESPACE__ . '\\Dotenv_Salts_Command' );


### Functions

/**
 * @param $key
 * @param $value
 *
 * @return string
 */
function format_line( $key, $value )
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
function get_filepath( $assoc_args )
{
    if ( empty( $assoc_args['file'] ) )
        return getcwd() . '/.env';

    if ( \WP_CLI\Utils\is_path_absolute( $assoc_args['file'] ) )
    {
        return realpath( $assoc_args['file'] );
    }

    return realpath(getcwd() . $assoc_args['file']);
}

/**
 * @param $args
 *
 * @return Dotenv_File
 */
function get_dotenv( $args )
{
    $filepath = get_filepath($args);

    return new Dotenv_File($filepath);
}

/**
 * @param $args
 *
 * @return Dotenv_File
 */
function get_dotenv_for_read_or_fail( $args )
{
    $dotenv = get_dotenv($args);

    if ( $dotenv->is_readable() ) return $dotenv->load();

    WP_CLI::error($dotenv->get_filepath() . ' is not readable! Check your file permissions.');
    exit;
}

/**
 * @param $args
 *
 * @return Dotenv_File
 */
function get_dotenv_for_write_or_fail( $args )
{
    $dotenv = get_dotenv_for_read_or_fail($args);

    if ( $dotenv->is_writable() ) return $dotenv; // already loaded

    WP_CLI::error($dotenv->get_filepath() . ' is not writable! Check your file permissions.');
    exit;
}

