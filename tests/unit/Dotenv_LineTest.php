<?php

use WP_CLI_Dotenv\Dotenv\Line;

class Dotenv_LineTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    function it_has_a_method_for_getting_the_key()
    {
        $line = new Line('FOO=BAR');
        $this->assertSame('FOO', $line->key());
    }

    /**
     * @test
     */
    function it_has_a_method_for_getting_the_value()
    {
        $line = new Line('FOO=BAR');
        $this->assertSame('BAR', $line->value());
    }

    /**
     * @test
     */
    function it_can_return_itself_as_a_string()
    {
        $line = new Line('FOO = Monkey stuff');
        $this->assertSame('FOO=Monkey stuff', $line->toString());
    }

    /**
     * @test
     */
    public function it_strips_matching_quotes_from_both_ends_of_the_value()
    {
        $lineWithSingleQuotedValue = new Line("FOO = 'BAR'");
        $lineWithDoubleQuotedValue = new Line('FOO = "BAR"');
        $lineWithMixedQuotedValue = new Line('FOO = \'BAR"');

        // value is wrapped with double quotes
    	$this->assertSame('BAR', $lineWithSingleQuotedValue->value());
        // value is wrapped with single quotes
        $this->assertSame('BAR', $lineWithDoubleQuotedValue->value());
        // value is wrapped with quotes that do not match - could be part of value itself
        $this->assertSame('\'BAR"', $lineWithMixedQuotedValue->value());
    }

    /**
     * @test
     */
    public function it_can_be_constructed_from_a_pair()
    {
    	$line = Line::fromPair('key', 'value');

        $this->assertSame('key', $line->key());
        $this->assertSame('value', $line->value());
    }

}
