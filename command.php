<?php namespace WP_CLI_Dotenv_Command;

if ( ! defined('WP_CLI') ) return;

\WP_CLI::add_command( 'dotenv', __NAMESPACE__ . '\\Dotenv_Command' );
\WP_CLI::add_command( 'dotenv salts', __NAMESPACE__ . '\\Dotenv_Salts_Command' );
