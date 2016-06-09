<?php

use WP_CLI_Dotenv_Command\Dotenv_File;

class Dotenv_FileTest extends PHPUnit_Framework_TestCase
{
    use Fixtures;
    
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
    function it_updates_existing_keys_or_adds_a_new_line()
    {
        $filepath = $this->get_fixture_path('env-basic');
        $dotenv   = new Dotenv_File($filepath);
        $dotenv->load();

        $this->assertEquals('BAR', $dotenv->get('FOO'));

        $dotenv->set('FOO', 'BAR-2');

        $this->assertEquals('BAR-2', $dotenv->get('FOO'));
        $this->assertEquals(1, $dotenv->size());

        $dotenv->set('SECRET', 'stuff');
        $this->assertEquals(2, $dotenv->size());
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
     * @test
     */
    public function it_can_clean_matching_quotes_from_both_ends_of_a_string()
    {
        // value is wrapped with double quotes
    	$this->assertSame('foo', Dotenv_File::clean_quotes('"foo"'));
        // value is wrapped with single quotes
    	$this->assertSame('bar', Dotenv_File::clean_quotes('\'bar\''));
        // value is wrapped with quotes that do not match - could be part of value itself
    	$this->assertSame('\'baz"', Dotenv_File::clean_quotes('\'baz"'));
    }
}
