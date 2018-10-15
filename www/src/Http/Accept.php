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
        $found = \preg_match_all("#(?<media_range>(\*/\*)|([a-z]+/[a-z|\*]+))(?<tokens>.*)#i", strtolower($accept), $match);
        $accepted = array_combine($match['media_range'], $match['tokens']);
        if ($found) {
            // Parse tokens
            foreach ($accepted as $media_range => $tokens) {
                $has_tokens = preg_match_all("#;\s*(?<keys>[a-z]+)\s*=\s*(?<values>[^;]+)#i", $tokens, $match_tokens);
                if ($has_tokens) {
                    $accepted[$media_range] = array_combine($match_tokens['keys'], array_map(function ($v) {
                        return is_numeric($v) ? $v + 0 : str_replace(["'", '"'], '', trim($v));
                    }, $match_tokens['values'])
                    );
                } else {
                    $accepted[$media_range] = ["q" => 1];
                }
            }
        }
        return $found ? $accepted : false;
    }
}
