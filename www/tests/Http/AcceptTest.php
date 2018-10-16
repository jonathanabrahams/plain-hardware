<?php
use PHPUnit\Framework\TestCase;

class AcceptTest extends TestCase
{
    public function test_create_accept()
    {
        $sut = new \App\Http\Accept("text/html");
        $this->assertInstanceOf(\App\Http\Accept::class, $sut);
    }

    public function provideAcceptHeadersExceptions()
    {
        return [
            [false, "text"],
        ];
    }

    public function provideAcceptHeaders()
    {
        return [
            [["text/html", ["q" => 1]], "text/html"],
            [["text/html", ["version" => 1, "q" => 1]], "text/html;version=1"],
            [["text/html", ["version" => 1, "q" => 0.8]], "text/html;version=1;q=0.8"],
            [["text/html", ["q" => 0.9]], "text/html;q=0.9"],
        ];
    }

    /**
     * @dataProvider provideAcceptHeaders
     */
    public function test_parse_good_accept_headers($expected, $accept)
    {
        $result = \App\Http\Accept::parse($accept);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider provideAcceptHeadersExceptions
     */
    public function test_parse_bad_accept_headers($expected, $accept)
    {
        $result = \App\Http\Accept::parse($accept);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider provideAcceptHeaders
     */
    public function test_create_accept_object($expected, $accept)
    {
        $result = \App\Http\Accept::create($accept);
        $this->assertInstanceOf(\App\Http\Accept::class, $result);
        list($media_range, $tokens) = $expected;
        foreach ($tokens as $key => $value) {
            $this->assertEquals($value, $result->getToken($key));
        }
        $this->assertEquals($media_range, $result->getMediaRange());
    }
}
