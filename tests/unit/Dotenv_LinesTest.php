<?php

use Illuminate\Support\Collection;
use WP_CLI_Dotenv_Command\Dotenv_Lines;

class Dotenv_LinesTest extends PHPUnit_Framework_TestCase
{
    use Fixtures;

    /**
     * @test
     */
    function it_is_a_collection()
    {
        $this->assertInstanceOf(Collection::class, new Dotenv_Lines);
    }

    /**
     * @test
     */
    function it_has_a_method_for_loading_from_a_file()
    {
        $filepath = $this->get_fixture_path('env-basic');
        $lines = Dotenv_Lines::fromFile($filepath);

        $this->assertEquals(count(file($filepath)), $lines->count());
    }

    /**
     * @test
     */
    function it_can_return_itself_as_a_single_string()
    {
        $filepath = $this->get_fixture_path('env-basic');
        $lines = Dotenv_Lines::fromFile($filepath);
        $contents = file_get_contents($filepath);

        $this->assertSame($contents, $lines->toString());
    }

    /**
     * @test
     */
    function it_can_return_itself_as_a_collection_of_key_value_pairs()
    {
        $filepath = $this->get_fixture_path('env-basic');
        $lines = Dotenv_Lines::fromFile($filepath);

        $this->assertSame('FOO', $lines->pairs()->first()->key());
        $this->assertSame('BAR', $lines->pairs()->first()->value());
    }


    /**
     * @test
     */
    // function it_parses_each_line_into_an_array_of_its_parts()
    // {
    //     $filepath = $this->get_fixture_path('env-basic');
    //     $lines = Dotenv_Lines::fromFile($filepath);
    //
    //     $keys = ['key','value','type'];
    //     $this->assertArraySubset($keys, array_keys($lines[0]));
    // }



    /**
     * @test
     */
    // public function it_can_remove_a_line_by_the_key()
    // {
    //     $filepath = $this->get_fixture_path('env-basic');
    //
    //     $this->assertEquals('BAR', $dotenv->get('FOO'));
    //
    //     $dotenv->remove('FOO');
    //
    //     $this->assertNull($dotenv->get('FOO'));
    // }

}
