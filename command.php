<?php

if (defined('WP_CLI') && class_exists('WP_CLI', false)) {
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
    }

    WP_CLI::add_command('dotenv', WP_CLI_Dotenv\WP_CLI\DotenvCommand::class);
    WP_CLI::add_command('dotenv salts', WP_CLI_Dotenv\WP_CLI\SaltsCommand::class);
}
