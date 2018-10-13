<?php namespace App\Http;

class AcceptHeader
{
    private $headers;

    public function __construct($headers)
    {
        $headers = explode(',', str_replace(' ', '', strtolower($headers)));
        $params = array_map(
            function ($type) {
                return Accept::create($type);
            },
            $headers
        );
        $this->headers = $params;
    }

    public static function create($headers)
    {
        return new self($headers);
    }

    public function accepts()
    {
        return $this->headers;
    }
}

class Accept
{
    private $type;
    private $sub_type;
    private $q=1;

    public function __construct($type, $sub_type, $q)
    {
    }

    public static function create($accept)
    {
        $items = explode(';', $accept);
        list($type, $sub_type) = explode('/', array_shift($items));
        return new self($type, $sub_type, $items);
    }
}
