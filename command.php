<?php

if (defined('WP_CLI') && class_exists('WP_CLI', false)) {
    WP_CLI::add_command('dotenv', WP_CLI_Dotenv_Command\Dotenv_Command::class);
    WP_CLI::add_command('dotenv salts', WP_CLI_Dotenv_Command\Dotenv_Salts_Command::class);
    require_once(__DIR__ . '/src/functions.php');
}
