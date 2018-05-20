<?php

if (defined('WP_CLI') && class_exists('WP_CLI', false)) {
    WP_CLI::add_command('dotenv', WP_CLI_Dotenv\WP_CLI\DotenvCommand::class);
    WP_CLI::add_command('dotenv salts', WP_CLI_Dotenv\WP_CLI\SaltsCommand::class);
}

class_alias(\WP_CLI_Dotenv\Dotenv\Collection::class, 'Illuminate\\Support\\Collection');
