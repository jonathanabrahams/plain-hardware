<?php namespace App\Http;

class AcceptHeader
{
    private $headers;

    public function __construct($headers)
    {
        $accepts = array_map(
            function ($type) {
                return Accept::create($type);
            },
            explode(',', strtolower($headers))
        );
        $this->accepts = self::sort($accepts);
    }

    public static function create($headers)
    {
        return new self($headers);
    }
    public function filter($headers)
    {
        return array_filter($this->accepts, function ($accept) use ($headers) {
            return $accept->isSatisfiedBy($headers);
        });
    }
    public static function sort($accepts)
    {
        \uasort($accepts, function ($a, $b) {
            return $a->getQuality() <=> $b->getQuality();
        });
        $keys = array_keys($accepts);
        $q = array_map(function ($i) {return $i->getQuality();}, $accepts);
        \array_multisort(
            // First Quality
            $q, \SORT_DESC,
            // Then appeared i.e. key
            $keys, \SORT_ASC,
            // Sorts Accepts by Q then key
            $accepts
        );
        return $accepts;
    }

    /**
     * Accept
     *
     * @return \App\Htpp\Accept[]
     */
    public function accepts()
    {
        return $this->accepts;
    }
}
