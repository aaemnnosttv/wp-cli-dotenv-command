<?php

use PHPUnit\Framework\TestCase;
use WP_CLI_Dotenv\Dotenv\Line;

class DotenvLineTest extends TestCase
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
        $this->assertSame('FOO = Monkey stuff', $line->toString());
        $line = new Line('Some line');
        $this->assertSame('Some line', $line->toString());
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
    public function it_can_determine_the_matching_quote_that_wraps_the_value()
    {
        $lineWithSingleQuotedValue = new Line("FOO = 'BAR'");
        $lineWithDoubleQuotedValue = new Line('FOO = "BAR"');
        $lineWithMixedQuotedValue = new Line('FOO = \'BAR"');

        // value is wrapped with double quotes
    	$this->assertSame('\'', $lineWithSingleQuotedValue->quote());
        // value is wrapped with single quotes
        $this->assertSame('"', $lineWithDoubleQuotedValue->quote());
        // value is wrapped with quotes that do not match - could be part of value itself
        $this->assertSame('', $lineWithMixedQuotedValue->quote());
    }
}
