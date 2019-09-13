<?php

namespace WP_CLI_Dotenv\Salts;

use Exception;
use WP_CLI_Dotenv\Dotenv\Collection;

class Salts
{
    /**
     * Pattern to match both key and value from php define statements.
     */
    const PATTERN_CAPTURE = "/'([^']+)'/";

    /**
     * Resource to load salts from.
     * @var string
     */
    protected $source;

    /**
     * Salts to be generated
     */
    protected $constant_list;

    /**
     * Salts constructor.
     *
     * @param string $source
     */
    public function __construct($source = 'https://api.wordpress.org/secret-key/1.1/salt/')
    {
        $this->source = $source;
        $this->constant_list = array(
            'AUTH_KEY',
            'SECURE_AUTH_KEY',
            'LOGGED_IN_KEY',
            'NONCE_KEY',
            'AUTH_SALT',
            'SECURE_AUTH_SALT',
            'LOGGED_IN_SALT',
            'NONCE_SALT'
        );
    }

    /**
     * Get a fresh set of salts as a collection.
     *
     * @throws Exception
     *
     * @return Collection
     */
    public function collect()
    {
        $salts = $this->salts();

        if ($salts->isEmpty()) {
            throw new Exception('There was a problem fetching salts from the WordPress generator service.');
        }

        return $salts;
    }

    /**
     * Generate salts, locally if possible.
     * May fetch from source.
     * 
     * @return Collection
     */
    protected function salts()
    {
        try {
            $secret_keys = new Collection();
            foreach ( $this->constant_list as $key ) {
                $secret_keys->push( trim( self::unique_key() ) );
            }
        } catch ( Exception $ex ) {
            $secret_keys = $this->fetch();
        }
        return $secret_keys;
    }

    /**
     * Fetch salts from the generator and return the parsed response.
     * Does not allow for extra salts to be set.
     *
     * @throws Exception
     *
     * @return Collection
     */
    protected function fetch()
    {
        return Collection::make(file($this->source, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES))
            ->map(function ($line) {
                return trim( substr( $line, 28, 64 ) );
            })->filter();
    }

    /**
     * Generate a single unique key for salts
     * 
     * Reference: https://github.com/wp-cli/config-command/blob/9e3ccb8f013a7332c16a78ac68f9deee171b022f/src/Config_Command.php#L625-L682
     * 
     * @throws Exception
     * 
     * @return string
     * 
     */
    protected static function unique_key()
    {
        if ( ! function_exists( 'random_int' ) ) {
            throw new Exception( "'random_int' does not exist" );
        }

        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
        $key = '';
        for ( $i = 0; $i < 64; $i++ ) {
            $key .= substr( $chars, random_int( 0, strlen( $chars ) - 1 ), 1 );
        }
        return $key;
    }
}
