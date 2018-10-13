<?php
namespace App {
    class Error
    {
        const ERR_CONTEXT = 'EH';
        const HTTP_ERR_DIR = VIEW_DIR.'/error';

        public static function handler($errno, $errstr, $errfile, $errline)
        {
            switch (true) {
                case ($errno & E_ERROR) != 0: $type = 'Error'; break;
                case ($errno & E_WARNING) != 0: $type = 'Warning'; break;
                case ($errno & E_PARSE) != 0: $type = 'Parse'; break;
                case ($errno & E_NOTICE) != 0: $type = 'Notice'; break;
                default: $type = 'Unknown';
            }
            \App\Error::header(new \App\Error\Context(self::ERR_CONTEXT, $type, $errno, $errstr));
            return true;
        }

        public static function header(Error\Context $context)
        {
            \header(sprintf(
                'X-%s-%s: %s:%s',
                $context->getContext(),
                $context->getType(),
                $context->getCode(),
                $context->getMessage()
            ));
        }

        public static function render($errno, Error\Context $context=null)
        {
            if ($context) {
                self::header($context);
            }
            
            // Render Error Response
            $headers = \App\Http\Accept::headers($_SERVER['HTTP_ACCEPT']);
            $select = $headers->select(['text/html','application/json']);
            var_dump($select);
            die();
            if (empty($select)) {
                \http_response_code(406);
                exit;
            }
            $err_file = self::HTTP_ERR_DIR.'/'.$errno.'.html';
            if (\file_exists($err_file)) {
                readfile($err_file);
            } else {
                echo $errno .':'. $context->getCode() . ':' . $context->getMessage();
            }
        }
    }
}
namespace App\Error {
    class Context
    {
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

        public static function thrown($context, \Throwable $e)
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
