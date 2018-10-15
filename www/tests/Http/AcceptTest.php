<?php
use PHPUnit\Framework\TestCase;

class AcceptTest extends TestCase
{
    public function test_create_accept()
    {
        $sut = new \App\Http\Accept("text", "html", 1);
        $this->assertInstanceOf(\App\Http\Accept::class, $sut);
    }

    public function provideAcceptHeaders()
    {
        return [
            [false, "text"],
            [["text/html" => ["q" => 1]], "text/html"],
            [["text/html" => ["version" => 1, "q" => 1]], "text/html;version=1"],
            [["text/html" => ["version" => 1, "q" => 0.8]], "text/html;version=1;q=0.8"],
            [["text/html" => ["q" => 0.9]], "text/html;q=0.9"],
        ];
    }

    /**
     * @dataProvider provideAcceptHeaders
     */
    public function test_list_headers($expected, $accept)
    {
        $result = \App\Http\Accept::parse($accept);
        $this->assertEquals($expected, $result);
    }
}
