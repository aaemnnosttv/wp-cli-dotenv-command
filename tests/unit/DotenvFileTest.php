<?php

use PHPUnit\Framework\TestCase;
use WP_CLI_Dotenv\Dotenv\Exception\FilePermissionsException;
use WP_CLI_Dotenv\Dotenv\Exception\NonExistentFileException;
use WP_CLI_Dotenv\Dotenv\File;

class DotenvFileTest extends TestCase
{
    use WP_CLI_Dotenv\Fixtures;

    /**
     * @test
     */
    public function it_loads_the_file()
    {
        $path = $this->get_fixture_path('env-basic');
        $env  = new File($path);

        $this->assertTrue($env->isReadable());
        $this->assertTrue($env->isWritable());
    }

    /**
     * @test
     */
    public function it_has_a_named_constructor_for_the_file_AT_the_given_path()
    {
        $env = File::at($this->get_fixture_path('env-basic'));

        $this->assertInstanceOf(File::class, $env);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_the_file_does_not_exist()
    {
        $this->expectException(NonExistentFileException::class);

        File::at($this->get_fixture_path('env-unreadable'));
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_the_file_is_not_readable()
    {
        $this->expectException(FilePermissionsException::class);

        $path = $this->copy_fixture('env-basic');
        chmod($path, 0000);
        File::at($path);
    }

    /**
     * @test
     */
    public function it_has_a_named_constructor_for_the_writable_file_at_the_given_path()
    {
        $env = File::writable($this->get_fixture_path('env-basic'));

        $this->assertInstanceOf(File::class, $env);
    }

    /**
     * @test
     *
     */
    public function it_throws_an_exception_if_the_file_is_not_writable()
    {
        $this->expectException(FilePermissionsException::class);

        $path = $this->get_fixture_path('env-unwritable');
        chmod($path, 0444);
        File::writable($path);
    }

    /**
     * @test
     */
    function it_can_create_a_new_file_at_the_given_path()
    {
        $path = $this->temp_path('new_env');
        $env = File::create($path);

        $this->assertTrue(file_exists($path));
        $this->assertTrue($env->exists());
        $this->assertSame($path, $env->path());
    }

    /**
     * @test
     */
    public function it_can_check_for_the_existence_of_a_defined_var_by_key()
    {
        $env = File::at($this->get_fixture_path('env-basic'));
        $env->load();

        $this->assertTrue($env->hasKey('FOO'));
        $this->assertFalse($env->hasKey('HAS-NOT'));
    }


    /**
     * @test
     */
    public function it_can_get_and_set_values_for_a_given_key()
    {
        $path = $this->get_fixture_path('env-basic');
        $env  = new File($path);
        $env->load();

        $this->assertSame('BAR', $env->get('FOO'));
        $this->assertNull($env->get('BAR'));

        $env->set('BAR', 'BAZ');

        $this->assertSame('BAZ', $env->get('BAR'));
    }

    /**
     * @test
     */
    function it_updates_existing_keys_or_adds_a_new_line()
    {
        $path = $this->get_fixture_path('env-basic');
        $env  = new File($path);
        $env->load();

        $this->assertSame('BAR', $env->get('FOO'));

        $env->set('FOO', 'BAR-2');

        $this->assertSame('BAR-2', $env->get('FOO'));
        $this->assertSame(2, $env->lineCount());

        $env->set('SECRET', 'stuff');
        $this->assertSame(3, $env->lineCount());
    }

    /**
     * @test
     */
    function it_can_save_changes_to_the_file()
    {
        $path = $this->copy_fixture('env-basic');
        $env  = new File($path);
        $env->load();

        $env->set('SAVE_TEST', 'SAVED');
        $env->save();

        $env->load();
        $this->assertSame('SAVED', $env->get('SAVE_TEST'));
    }

    /**
     * @test
     */
    public function it_does_not_write_to_the_file_until_save_is_called()
    {
        $path = $this->copy_fixture('env-basic');
        $env  = new File($path);
        $env->load();

        $this->assertNull($env->get('SOME_KEY'));
        $env->set('SOME_KEY', 'totally set');

        $this->assertSame('totally set', $env->get('SOME_KEY'));
        $this->assertSame('integer', gettype($env->save()));
        $env->set('SOME_KEY', 'this will be wiped out once we load in a second');

        // refresh the instance from the file
        $env->load();
        $this->assertSame('totally set', $env->get('SOME_KEY'));
    }

    /**
     * @test
     */
    public function it_can_remove_a_line_by_the_key()
    {
        $path = $this->get_fixture_path('env-basic');
        $env  = new File($path);
        $env->load();

        $this->assertSame('BAR', $env->get('FOO'));

        $env->remove('FOO');

        $this->assertNull($env->get('FOO'));
    }

    /**
     * @test
     */
    public function it_can_count_how_many_total_lines_the_file_has()
    {
        $path = $this->get_fixture_path('env-one-line-one-comment');
        $env  = new File($path);
        $env->load();

        $this->assertSame(2, $env->lineCount());
    }

    /**
     * @test
     */
    public function it_can_return_an_array_of_key_value_line_pairs()
    {
        $path = $this->get_fixture_path('env-basic');
        $env  = new File($path);
        $env->load();

        $this->assertCount(1, $env->dictionary());
    }
}
