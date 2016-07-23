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
        $tmp_root = sys_get_temp_dir() . '/wp-cli-dotenv-command';

        if (! is_dir($tmp_root)) {
            mkdir($tmp_root);
        }

        $new_path = "$tmp_root/$filename-" . uniqid() . '.tmp';

        copy($path, $new_path);

        return $new_path;
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
