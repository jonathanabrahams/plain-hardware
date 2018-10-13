<?php namespace App\Http;

class Accept
{
    private $type;
    private $sub_type;
    private $q=1;

    public function __construct($type, $sub_type, $q)
    {
        $this->type = $type;
        $this->sub_type = $sub_type;
    }

    public static function create($accept)
    {
        $items = explode(';', $accept);
        list($type, $sub_type) = explode('/', array_shift($items));
        return new self($type, $sub_type, $items);
    }
}
