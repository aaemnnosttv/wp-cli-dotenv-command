<?php

use PHPUnit\Framework\TestCase;
use WP_CLI_Dotenv\Dotenv\FileLines;

class DotenvFileLinesTest extends TestCase
{
    use WP_CLI_Dotenv\Fixtures;

    /**
     * @test
     */
    function it_has_a_method_for_loading_from_a_file()
    {
        $path = $this->get_fixture_path('env-basic');
        $lines = FileLines::load($path);

        $this->assertEquals(count(file($path)), $lines->count());
    }

    /**
     * @test
     */
    function it_can_return_itself_as_a_single_string()
    {
        $path = $this->get_fixture_path('env-basic');
        $lines = FileLines::load($path);
        $contents = file_get_contents($path);

        $this->assertSame($contents, $lines->toString());
    }

    /**
     * @test
     */
    function it_can_return_itself_as_a_collection_of_key_value_pairs()
    {
        $path = $this->get_fixture_path('env-basic');
        $lines = FileLines::load($path);

        $this->assertSame('FOO', $lines->pairs()->first()->key());
        $this->assertSame('BAR', $lines->pairs()->first()->value());
    }

    /**
     * @test
     */
     function it_can_remove_a_line_by_the_key()
     {
         $path = $this->get_fixture_path('env-basic');
         $lines = FileLines::load($path);

         $this->assertEquals('BAR', $lines->getDefinition('FOO'));

         $lines->removeDefinition('FOO');

         $this->assertEmpty($lines->getDefinition('FOO'));
     }

     /**
      * @test
      */
     function it_can_return_a_subset_using_globs_to_match_keys()
     {
         $lines = FileLines::fromArray([
            'FOO=BAR',
            'FOX=red',
            'FOOD=hot bar',
         ]);

         $this->assertSame(
             [
                 'FOO'  => 'BAR',
                 'FOOD' => 'hot bar'
             ],
             $lines->whereKeysLike('FOO*')->toDictionary()->all()
         );

         $this->assertSame(
             [
                 'FOO' => 'BAR',
                 'FOX' => 'red'
             ],
             $lines->whereKeysLike('FO[OX]')->toDictionary()->all()
         );

         $this->assertSame(
             [
                 'FOO' => 'BAR',
                 'FOX' => 'red'
             ],
             $lines->whereKeysLike(['FOO', 'FOX'])->toDictionary()->all()
         );

     }

}
