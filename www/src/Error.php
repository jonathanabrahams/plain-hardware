<?php
namespace App {
    class Error
    {
        const ERR_CONTEXT = 'EH';
        const HTTP_ERR_DIR = VIEW_DIR . '/error';

        public static function handler($errno, $errstr, $errfile, $errline)
        {
            switch (true) {
                case ($errno & E_ERROR) != 0:$type = 'Error';
                    break;
                case ($errno & E_WARNING) != 0:$type = 'Warning';
                    break;
                case ($errno & E_PARSE) != 0:$type = 'Parse';
                    break;
                case ($errno & E_NOTICE) != 0:$type = 'Notice';
                    break;
                default:$type = 'Unknown';
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

        public static function render($errno, Error\Context $context = null)
        {
            if ($context) {
                self::header($context);
            }

            // Render Error Response
            $headers = \App\Http\AcceptHeader::create($_SERVER['HTTP_ACCEPT']);
            $accepts = $headers->filter(['text/html', 'application/json']);
            if (empty($accepts)) {
                \http_response_code(406);
                $err_file = self::HTTP_ERR_DIR . '/' . $errno . '.html';
                if (\file_exists($err_file)) {
                    readfile($err_file);
                }
                exit;
            }
            if (current($accepts)->isSatisfiedBy('text/html')) {
                http_response_code($errno);
                $err_file = self::HTTP_ERR_DIR . '/' . $errno . '.html';
                if (\file_exists($err_file)) {
                    readfile($err_file);
                } else {
                    echo <<< HTML
<html>
<body>
<h1>$errno</h1>
<h2>Context</h2>
<p>
Code: $context->getCode()<br/>
Message: $context->getMessage()
</p>
</body>
</html>
HTML;
                }
            } else if (current($accepts)->isSatisfiedBy('application/json')) {
                http_response_code($errno);
                header('Content-Type: applicatino/json');
                $err_file = self::HTTP_ERR_DIR . '/' . $errno . '.json';
                if (\file_exists($err_file)) {
                    readfile($err_file);
                } else {
                    echo json_encode([
                        "errors" => [
                            "status" => $errno,
                            "code" => $context->getCode(),
                            "context" => $context->getMessage(),
                        ],
                    ]);
                }
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
