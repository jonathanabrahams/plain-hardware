<?php namespace App\Http;

class Accept {
    private $headers;

    public function __construct($headers)
    {
        $this->headers = $headers;
    }

    static function headers($headers)
    {
        return new self($headers);
    }

    public function hasType($type)
    {
        return stripos($this->headers, $type) !== false;
    }
}