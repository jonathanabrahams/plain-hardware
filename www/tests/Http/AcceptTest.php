<?php
use PHPUnit\Framework\TestCase;

class AcceptTest extends TestCase
{
    public function test_create_accept()
    {
        $sut =  new \App\Http\Accept("text", "html", 1);
        $this->assertInstanceOf(\App\Http\Accept::class, $sut);
    }

    public function test_list_headers()
    {
        \App\Http\Accept::parse("*/*; q=1; level=3; version=1.2.3");
        \App\Http\Accept::parse("*/html; q=1; level=3; version=1.2.3");
        \App\Http\Accept::parse("text/*; q=1; level=3; version=1.2.3");
        \App\Http\Accept::parse("text/html; q=1; level=3; version=1.2.3");
        
        \App\Http\Accept::parse("text/html");
        \App\Http\Accept::parse("text/html;");
    }
}
