<?php

namespace WP_CLI_Dotenv\Salts;

use WP_CLI_Dotenv\Dotenv\Collection;

interface SaltProviderInterface
{
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
	public function salt(): string;
}