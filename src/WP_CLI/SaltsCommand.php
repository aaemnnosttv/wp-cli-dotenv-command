<?php

namespace WP_CLI_Dotenv\WP_CLI;

use Exception;
use WP_CLI;
use WP_CLI_Dotenv\Dotenv\Collection;
use WP_CLI_Dotenv\Dotenv\File;
use WP_CLI_Dotenv\Salts\RandomIntSaltProvider;
use WP_CLI_Dotenv\Salts\Salts;

/**
 * Manage WordPress salts in .env format
 */
class SaltsCommand extends Command
{
    /**
     * The target environment file.
     * @var File
     */
    protected $env;

    /**
     * Salts parsed from generator service.
     * @var Collection
     */
    protected $salts;

    /**
     * Fetch some fresh salts and add them to the environment file if they do not already exist.
     *
     * [--file=<path-to-dotenv>]
     * : Path to the environment file.  Default: '.env'
     *
     * [--force]
     * : Overwrite any existing salts in the environment file.
     *
     * @when before_wp_load
     *
     * @param $_
     * @param $assoc_args
     */
    public function generate($_, $assoc_args)
    {
        $this->init_args(func_get_args());

        $updated = $this->update_salts($this->get_flag('force') ?: $this->salts_are_placeholders());

        if (! $this->env->save()) {
            WP_CLI::error('Failed to update salts.');
        }

        $skipped = $updated->pluck('skipped')->filter();
        $set = $this->salts->count() - $skipped->count();

        if ($set === count($this->salts)) {
            WP_CLI::success('Salts generated.');
        } elseif ($set) {
            WP_CLI::success("$set salts set.");
        }

        if (! $skipped->isEmpty()) {
            WP_CLI::line('Some keys were already defined in the environment file.');
            WP_CLI::line("Use 'dotenv salts regenerate' to update them.");
        }
    }

    /**
     * Regenerate salts for the environment file.
     *
     * [--file=<path-to-dotenv>]
     * : Path to the environment file.  Default: '.env'
     *
     * @when before_wp_load
     *
     * @param $_
     * @param $assoc_args
     */
    public function regenerate($_, $assoc_args)
    {
        $this->init_args(func_get_args());

        $this->update_salts(true);

        if (! $this->env->save()) {
            WP_CLI::error('Failed to update salts.');
        }

        WP_CLI::success('Salts regenerated.');
    }

    /**
     * Perform a simple check to see if all defined salts are using a placeholder.
     * @return bool
     */
    protected function salts_are_placeholders()
    {
        return $this->env->dictionary()
            // salts are stored as a list [key, value]
            ->only($this->salts->pluck(0)->all()) // strip the env down to just the salts
            ->values()
            ->unique()
            ->count() === 1; // 1 unique means they are all the same
    }

    /**
     * Update salts in the environment file.
     *
     * @param bool $force Whether or not to force update any existing values
     *
     * @return Collection
     */
    protected function update_salts($force = false)
    {
        return $this->salts->map(function ($salt) use ($force) {
            list($key, $value) = $salt;

            if (! $force && $this->env->hasKey($key)) {
                WP_CLI::line("The '$key' already exists, skipping.");
                $salt['skipped'] = true;
                return $salt;
            }

            $this->env->set($key, $value, "'");

            return $salt;
        });
    }

    /**
     * Initialize properties for the command.
     *
     * @param array $args
     */
    protected function init_args($args)
    {
        parent::init_args($args);

        $this->env = $this->get_env_for_write_or_fail();
        
        if(function_exists('random_int')) {
            $api = new Salts(new RandomIntSaltProvider());
        } else {
            $api = new Salts(null);
        }

        try {
            $this->salts = $api->collect();
        } catch (Exception $e) {
            WP_CLI::error($e->getMessage());
        }
    }
}
