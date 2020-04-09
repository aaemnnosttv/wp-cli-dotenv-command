<?php

namespace WP_CLI_Dotenv\Salts;

use Exception;

class RandomIntSaltProvider implements SaltProviderInterface
{
	/**
	 * 
     */
	public function salt(): string
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