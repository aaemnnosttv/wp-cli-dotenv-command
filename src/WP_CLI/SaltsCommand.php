<?php

namespace WP_CLI_Dotenv\WP_CLI;

use Exception;
use Illuminate\Support\Collection;
use WP_CLI;
use WP_CLI_Dotenv\Dotenv\File;
use WP_CLI_Dotenv\Salts\Salts;

/**
 * Manage WordPress salts in .env format
 */
class SaltsCommand extends Command
{
    /**
     * Fetch some fresh salts and add them to the environment file if they do not already exist
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
    function generate($_, $assoc_args)
    {
        $this->init_args(func_get_args());
        $env = $this->get_env_for_write_or_fail();

        try {
            $salts = Salts::collect();
        } catch (Exception $e) {
            WP_CLI::error($e->getMessage());
            return;
        }

        $updated = $this->update_salts($salts, $env, $this->get_flag('force'));

        if (! $env->save()) {
            WP_CLI::error('Failed to update salts.');
            exit;
        }

        $skipped = $updated->pluck('skipped');
        $set = $salts->count() - $skipped->count();

        if ($set === count($salts)) {
            WP_CLI::success('Salts generated.');
        } elseif ($set) {
            WP_CLI::success("$set salts set.");
        }

        if ($skipped) {
            WP_CLI::line('Some keys were already defined in the environment file.');
            WP_CLI::line("Use 'dotenv salts regenerate' to update them.");
        }
    }

    /**
     * Regenerate salts for the environment file
     *
     * [--file=<path-to-dotenv>]
     * : Path to the environment file.  Default: '.env'
     *
     * @when before_wp_load
     *
     * @param $_
     * @param $assoc_args
     */
    function regenerate($_, $assoc_args)
    {
        $this->init_args(func_get_args());
        $env = $this->get_env_for_write_or_fail();

        try {
            $salts = Salts::collect();
        } catch (Exception $e) {
            WP_CLI::error($e->getMessage());
            exit;
        }

        $this->update_salts($salts, $env, true);

        if (! $env->save()) {
            WP_CLI::error('Failed to update salts.');
            exit;
        }

        WP_CLI::success('Salts regenerated.');
    }

    /**
     * @param Collection $salts Salts collection
     * @param File       $file  Environment file
     * @param bool       $force Whether or not to force update any existing values
     *
     * @return Collection
     */
    protected function update_salts(Collection $salts, File $file, $force = false)
    {
        return $salts->map(function ($salt) use ($file, $force) {
            list($key, $value) = $salt;

            if (! $force && $file->has_key($key)) {
                WP_CLI::line("The '$key' already exists, skipping.");
                $salt['skipped'] = true;
                return $salt;
            }

            $file->set($key, $value, "'");

            return $salt;
        });
    }
}
