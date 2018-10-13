<?php
use PHPUnit\Framework\TestCase;

class AcceptTest extends TestCase
{
    public function standardHeader()
    {
        return [
            "standard" => ["text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8"]
        ];
    }
    /**
     * @dataProvider standardHeader
     */
    public function test_create_accept($headers)
    {
        $sut =  \App\Http\AcceptHeader::create($headers);
        $this->assertInstanceOf(\App\Http\AcceptHeader::class, $sut);
    }

    /**
     * @dataProvider standardHeader
     */
    public function test_list_headers($headers)
    {
        $sut =  \App\Http\AcceptHeader::create($headers);
        $accepts = $sut->accepts();
        $this->assertEquals(["*/*"=>[]], $accepts[5]);
    }
}
