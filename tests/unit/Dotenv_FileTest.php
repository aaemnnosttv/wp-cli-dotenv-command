<?php

use WP_CLI_Dotenv_Command\Dotenv_File;

class Dotenv_FileTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_loads_the_file()
    {
        $filepath = $this->get_fixture_path('env-basic');
        $dotenv = new Dotenv_File($filepath);

        $this->assertFileExists($dotenv->get_filepath());
        $this->assertTrue($dotenv->exists());
        $this->assertTrue($dotenv->is_readable());
        $this->assertTrue($dotenv->is_writable());
        $this->assertEquals('env-basic', $dotenv->get_filename());
    }

    /**
     * @test
     */
    public function it_can_get_and_set_values_for_a_given_key()
    {
        $filepath = $this->get_fixture_path('env-basic');
        $dotenv = new Dotenv_File($filepath);
        $dotenv->load();

        $this->assertEquals('BAR', $dotenv->get('FOO'));
        $this->assertNull($dotenv->get('BAR'));
        $this->assertTrue($dotenv->set('BAR', 'BAZ'));
        $this->assertEquals('BAZ', $dotenv->get('BAR'));
    }

    /**
     * @test
     */
    public function it_does_not_write_to_the_file_until_save_is_called()
    {
        $filepath = $this->copy_fixture('env-basic');
        $dotenv = new Dotenv_File($filepath);
        $dotenv->load();

        $this->assertNull($dotenv->get('SOME_KEY'));
        $dotenv->set('SOME_KEY', 'totally set');

        $this->assertEquals('totally set', $dotenv->get('SOME_KEY'));
        $this->assertEquals('integer', gettype($dotenv->save()));
        $dotenv->set('SOME_KEY', 'this will be wiped out once we load in a second');

        // refresh the instance from the file
        $dotenv->load();
        $this->assertEquals('totally set', $dotenv->get('SOME_KEY'));

        unlink($filepath);
    }
    /**
     * @test
     */
    public function it_can_remove_a_line_by_the_key()
    {
        $filepath = $this->get_fixture_path('env-basic');
        $dotenv = new Dotenv_File($filepath);
        $dotenv->load();

        $this->assertEquals('BAR', $dotenv->get('FOO'));

        $dotenv->remove('FOO');

        $this->assertNull($dotenv->get('FOO'));
    }

    /**
     * @test
     */
    public function it_can_count_how_many_total_lines_the_file_has()
    {
        $filepath = $this->get_fixture_path('env-one-line-one-comment');
        $dotenv = new Dotenv_File($filepath);
        $dotenv->load();

        $this->assertEquals(2, $dotenv->size());
    }

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
//        $new_path = 'php://memory/' . microtime(false) . uniqid();

        copy($filepath, $new_path);

        return $new_path;
    }

    protected function get_fixture_path($path)
    {
        return realpath(__DIR__ . '/../fixtures/' . $path);
    }
}