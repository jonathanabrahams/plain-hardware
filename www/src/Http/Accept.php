<?php namespace App\Http;

class Accept
{
    private $type;
    private $sub_type;
    /**
     * Quality Values
     *
     * @var integer
     */
    private $q = 1;
    /**
     * Key/Value pair paraments
     *
     * @var array
     */
    private $parameters = array();

    public function __construct($type, $sub_type, $q = 1, $parameters = [])
    {
        $this->type = $type;
        $this->sub_type = $sub_type;
        $this->q = $q;
        $this->parameters = $parameters;
    }

    public function accept()
    {
        return $this->type . " " . $this->sub_type . " " . $this->q . " " . json_encode($this->parameters);
    }

    public static function parse($accept)
    {
        // Parse media range
        $found = \preg_match("#(?<media_range>(\*/\*)|([a-z]+/[a-z|\*]+))(?<tokens>.*)#i", strtolower($accept), $match);
        if ($found) {
            // Parse tokens
            $media_range = $match['media_range'];
            $tokens = $match['tokens'];
            $has_tokens = preg_match_all("#;\s*(?<keys>[a-z]+)\s*=\s*(?<values>[^;]+)#i", $tokens, $match_tokens);
            if ($has_tokens) {
                $accepted = array_combine(
                    $match_tokens['keys'],
                    array_map(
                        function ($v) {
                            return is_numeric($v) ? doubleval($v) : str_replace(["'", '"'], '', trim($v));
                        },
                        $match_tokens['values']
                    )
                );
                if (!array_key_exists('q', $accepted)) {
                    $accepted['q'] = 1;
                }
            } else {
                $accepted = ["q" => 1];
            }
        }
        return $found ? [$media_range => $accepted] : false;
    }
}
