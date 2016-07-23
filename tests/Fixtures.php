<?php

namespace WP_CLI_Dotenv;

trait Fixtures
{
    /**
     * Copies the fixture file to a new file with a unique name
     *
     * @param $filename
     *
     * @return string absolute path to new file
     */
    protected function copy_fixture($filename)
    {
        $path = $this->get_fixture_path($filename);
        $new_path = $this->temp_path($filename);

        copy($path, $new_path);

        return $new_path;
    }

    /**
     * Get a disposable file path for the given filename.
     *
     * @param $filename
     *
     * @return string
     */
    protected function temp_path($filename)
    {
        $tmp_root = sys_get_temp_dir() . '/wp-cli-dotenv-command';

        if (! is_dir($tmp_root)) {
            mkdir($tmp_root);
        }

        return "$tmp_root/$filename-" . uniqid() . '.tmp';
    }

    /**
     * Get the absolute path to a fixture file.
     *
     * @param  string $path  Relative path to fixture
     *
     * @return string
     */
    protected function get_fixture_path($path)
    {
        return __DIR__ . '/fixtures/' . $path;
    }
}
