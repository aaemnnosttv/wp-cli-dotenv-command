<?php

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
     * [get_fixture_path description]
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    protected function get_fixture_path($path)
    {
        return __DIR__ . '/fixtures/' . $path;
    }
}
