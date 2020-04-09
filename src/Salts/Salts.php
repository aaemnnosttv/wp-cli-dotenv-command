<?php

namespace WP_CLI_Dotenv\Salts;

use Exception;
use WP_CLI_Dotenv\Dotenv\Collection;
use WP_CLI_Dotenv\Salts\SaltProviderInterface;

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
     * Salt provider
     */
    private $saltProvider;

    /**
     * Salts constructor.
     *
     * @param string $source
     */
    public function __construct($saltProvider = null, $source = 'https://api.wordpress.org/secret-key/1.1/salt/')
    {
        $this->source = $source;
        $this->saltProvider = $saltProvider;
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
            throw new Exception('No resource exists for generating salts.');
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
        if(!empty($this->saltProvider)) {
            $secret_keys = new Collection();
            foreach ( $this->constant_list as $key ) {
                $secret_keys->push( array(
                    $key,
                    trim( $this->saltProvider->salt() ),
                ) );
            }
        } else {
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
    public function fetch()
    {
        return Collection::make(file($this->source, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES))
            ->map(function ($line) {
                // capture everything between single quotes
                preg_match_all(self::PATTERN_CAPTURE, $line, $matches);
                // matches[x]
                //   0 - complete match
                //   1 - captures
                return $matches[ 1 ];
            })->filter();
    }
}
