<?php 
namespace App {
    class Error {
        const HTTP_ERR_DIR = VIEW_DIR.'/error';

        static function handler($errno, $errstr, $errfile, $errline)
        {
            switch( true )
            {
                case ($errno & E_ERROR) != 0 : $type = 'Error'; break;
                case ($errno & E_WARNING ) != 0 : $type = 'Warning'; break;
                case ($errno & E_PARSE ) != 0 : $type = 'Parse'; break;
                case ($errno & E_NOTICE ) != 0 : $type = 'Notice'; break;
                default: $type = 'Unknown';
            }
            \App\Error::header(new \App\Error\Context('EH', $type, $errno, $errstr));
            return true;
        }

        static function header(Error\Context $context)
        {
            \header(sprintf('X-%s-%s: %s:%s', 
                $context->getContext(),
                $context->getType(),
                $context->getCode(),
                $context->getMessage()
            ));
        }

        static function render($errno, Error\Context $context=null)
        {
            if( $context ) {
                self::header($context);
            }
            
            // Render Error Response
            $accept_header = self::acceptHeaderParser();
            $accepted = self::acceptedTypes($accept_header, ['text/html','application/json']);
            var_dump($accepted);
            if( empty($accepted) ) {
                \http_response_code(406);
                exit;
            }
            
            $err_file = self::HTTP_ERR_DIR.'/'.$errno.'.html';
            if( \file_exists($err_file) ){
                readfile($err_file);
            } else {
                echo $errno .':'. $context->getCode() . ':' . $context->getMessage();
            }
        }
        
        static function acceptHeaderParser($header = null)
        {
            $header = $header??$_SERVER['HTTP_ACCEPT'];
            $accept_types = [];
            $accept = strtolower(str_replace(' ', '', $header));
            $accept = explode(',', $accept);
            foreach ($accept as $a) {
                $q = 1;
                if (strpos($a, ';q=')) {
                    list($a, $q) = explode(';q=', $a);
                }
                $accept_types[$a] = $q;
            }
            arsort($accept_types);
            return $accept_types;
        }
        static function acceptedTypes($accept_header, $acceptable_types)
        {
            return array_filter($acceptable_types, function($type) use( &$accept_header) {
                // Type/SubType checks
                list($t, $st) = (array)explode('/',$type);
                $ah = array_map(function($aht){ 
                    return ($p = stripos($aht,';')) !== false ? substr($aht,0,$p) : $aht;
                },array_keys($accept_header));
            var_dump($ah);
                // EXACT
                if( array_key_exists($type, $accept_header) ){
                    return true;
                }
                // Type/*
                if( array_key_exists( $t.'/*', $accept_header) ) {
                    return true;
                }
                // Any
                if( array_key_exists('*/*', $accept_header) ) {
                    return true;
                }
                return false;
            });
        }
    }
}
namespace App\Error { 
    class Context {
        private $context = "CTX";
        private $type = "T";
        private $code = 0;
        private $message = "Unknown";
        
        public function __construct($context, $type, $code, $message)
        {
            $this->context = $context;
            $this->type = $type;
            $this->code = $code;
            $this->message = $message;
        }

        static function thrown($context, \Throwable $e)
        {
            return new self($context, get_class($e), $e->getCode(), $e->getMessage());
        }

        public function getContext()
        {
            return $this->context;
        }

        public function getType()
        {
            return $this->type;
        }

        public function getCode()
        {
            return $this->code;
        }

        public function getMessage()
        {
            return $this->message;
        }
    }
}