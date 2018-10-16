<?php
use PHPUnit\Framework\TestCase;

class AcceptHeaderTest extends TestCase
{
    public function test_accept_header()
    {
        $sut = new \App\Http\AcceptHeader("text/html");
        $this->assertInstanceOf(\App\Http\AcceptHeader::class, $sut);
    }

    public function test_create_accept_header_list()
    {
        $sut = \App\Http\AcceptHeader::create("text/xml;q=0.8,text/html,text/plain,application/json;q=0.9");

        $this->assertInstanceOf(\App\Http\AcceptHeader::class, $sut);
        $accepts = $sut->accepts();
        $this->assertEquals('text/html', current($accepts)->getMediaRange());
        $this->assertEquals('text/plain', next($accepts)->getMediaRange());
        $this->assertEquals('application/json', next($accepts)->getMediaRange());
        $this->assertEquals('text/xml', next($accepts)->getMediaRange());
    }

    public function test_is_satisfied_by_accept()
    {
        $headers = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8';
        $sut = \App\Http\AcceptHeader::create($headers);
        $accepted = array_filter($sut->accepts(), function ($accept) {
            return $accept->isSatisfiedBy('text/html');
        });
        $this->assertNotEmpty($accepted);
        $this->assertEquals('text/html', current($accepted)->getMediaRange());
    }
}
