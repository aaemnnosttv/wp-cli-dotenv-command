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
        $dotenv   = new Dotenv_File($filepath);

        $this->assertFileExists($dotenv->get_filepath());
        $this->assertTrue($dotenv->exists());
        $this->assertTrue($dotenv->is_readable());
        $this->assertTrue($dotenv->is_writable());
        $this->assertEquals('env-basic', $dotenv->get_filename());
    }

    /**
     * @test
     */
    public function it_has_a_named_constructor_for_the_file_AT_the_given_path()
    {
        $dotenv = Dotenv_File::at($this->get_fixture_path('env-basic'));

        $this->assertInstanceOf(Dotenv_File::class, $dotenv);
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function it_throws_an_exception_if_the_file_is_not_readable()
    {
        Dotenv_File::at($this->get_fixture_path('env-unreadable'));
    }

    /**
     * @test
     */
    public function it_has_a_named_constructor_for_the_writable_file_at_the_given_path()
    {
        $dotenv = Dotenv_File::writable($this->get_fixture_path('env-basic'));

        $this->assertInstanceOf(Dotenv_File::class, $dotenv);
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function it_throws_an_exception_if_the_file_is_not_writable()
    {
        $filepath = $this->get_fixture_path('env-unwritable');
        chmod($filepath, 0444);
        Dotenv_File::writable($filepath);
    }

    /**
     * @test
     */
    public function it_can_get_and_set_values_for_a_given_key()
    {
        $filepath = $this->get_fixture_path('env-basic');
        $dotenv   = new Dotenv_File($filepath);
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
        $dotenv   = new Dotenv_File($filepath);
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
        $dotenv   = new Dotenv_File($filepath);
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
        $dotenv   = new Dotenv_File($filepath);
        $dotenv->load();

        $this->assertEquals(2, $dotenv->size());
    }

    /**
     * @test
     */
    public function it_can_return_an_array_of_key_value_line_pairs()
    {
        $filepath = $this->get_fixture_path('env-basic');
        $dotenv   = new Dotenv_File($filepath);
        $dotenv->load();

        $this->assertSame($dotenv->get_pairs(), ['FOO' => 'BAR']);
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