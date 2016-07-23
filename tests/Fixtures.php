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
        $filepath = $this->get_fixture_path($filename);
        $new_path = $filepath . microtime(false) . uniqid();
        // $new_path = 'php://memory/' . microtime(false) . uniqid();

        copy($filepath, $new_path);

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
