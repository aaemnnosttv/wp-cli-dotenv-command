<?php

namespace WP_CLI_Dotenv;

use WP_CLI_Dotenv\Salts\SaltProviderInterface;


class DeterministicSaltProvider implements SaltProviderInterface
{
	public function salt(): string
	{
		return 'arandomstring';
	}
}