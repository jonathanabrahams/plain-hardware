<?php namespace App\Http;

class Accept
{
    /**
     * Media range
     *
     * Type/SubType
     *
     * @var string
     */
    private $media_range = 'text/html';
    /**
     * Quality Values
     *
     * 0 - 1
     *
     * @var double
     */
    private $q = 1;
    /**
     * Key/Value pair tokens
     *
     * @var array
     */
    private $tokens = array();

    public function __construct($media_range, $q = 1, $tokens = [])
    {
        $this->setMediaRange($media_range);
        $this->setQuality($q);
        $this->setTokens($tokens);
    }
    public function validMediaRange($media_range)
    {
        return preg_match('#(\*/\*)|([a-z|\+]+/[a-z|\+|\*]+)#i', $media_range);
    }
    public function setMediaRange($media_range)
    {
        if ($this->validMediaRange($media_range)) {
            $this->media_range = $media_range;
        } else {
            throw new \Exception("Invalid media range format, should be TYPE/SUBTYPE");
        }
        return $this;
    }
    public function getMediaRange()
    {
        return $this->media_range;
    }
    public function setQuality($q)
    {
        if (\is_numeric($q) && $q >= 0 && $q <= 1) {
            $this->q = $q;
        } else {
            throw new \OutOfBoundsException("Invalid quality, should be between 0 - 1");
        }
        return $this;
    }
    public function getQuality()
    {
        return $this->q;
    }
    public function setTokens($tokens)
    {
        $this->tokens = $tokens;
        return $this;
    }

    public function getTokens()
    {
        return $this->tokens;
    }

    public function getToken($token)
    {
        return array_key_exists($token, $this->tokens) ? $this->tokens[$token] : null;
    }
    /**
     * Create Accept
     *
     * @param [type] $accept
     *
     * @return void
     */
    public static function create($accept)
    {
        $parsed = self::parse($accept);
        if ($parsed !== false) {
            list($media_range, $tokens) = $parsed;
            return new self($media_range, $tokens['q'], $tokens);
        } else {
            throw new \Exception('Could not parse accept');
        }
    }

    public static function parse($accept)
    {
        // Parse media range
        $found = \preg_match("#(?<media_range>(\*/\*)|([a-z|\+]+/[a-z|\+|\*]+))(?<tokens>.*)#i", strtolower($accept), $match);
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
        return $found ? [$media_range, $accepted] : false;
    }

    public function isSatisfiedBy($accept)
    {
        if ($this->validMediaRange($accept)) {
            list($test_type, $test_sub_type) = explode('/', $accept);
            // Any
            if ($test_type == '*' && $test_sub_type == '*') {
                return true;
            }
            
            list($type, $sub_type) = explode('/', $this->getMediaRange());
            // Type
            if (strcmp($test_type, $type) === 0) {
                // Any SubType
                if ($test_sub_type === '*') {
                    return true;
                }
                // Exact SubType
                else if (strcmp($test_sub_type, $sub_type) === 0) {
                    return true;
                }
                // Invalid SubType
                else {
                    return false;
                }
            }
        }
        return false;
    }
}
