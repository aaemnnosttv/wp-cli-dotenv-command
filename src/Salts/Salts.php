<?php

namespace WP_CLI_Dotenv\Salts;

use Exception;
use Illuminate\Support\Collection;

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
     * Salts constructor.
     *
     * @param string $source
     */
    public function __construct($source = 'https://api.wordpress.org/secret-key/1.1/salt/')
    {
        $this->source = $source;
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
        $salts = $this->fetch();

        if ($salts->isEmpty()) {
            throw new Exception('There was a problem fetching salts from the WordPress generator service.');
        }

        return $salts;
    }

    /**
     * Fetch the salts from the generator and return the parsed response.
     *
     * @throws Exception
     *
     * @return Collection
     */
    protected function fetch()
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
