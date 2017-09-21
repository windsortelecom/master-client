<?php namespace Windsor\Master;

use Throwable;

class Exception extends \Exception
{
    protected $type = 'unknown';
    protected $statusCode = 500;
    protected $errors = [];

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $statusCode = null;
        $type = null;
        $errors = null;

        if (is_array($message)) {
            if (isset($message['code'])) {
                $code = $message['code'];
            }

            if (isset($message['type'])) {
                $type = $message['type'];
            }

            if (isset($message['status'])) {
                $statusCode = $message['status'];
            }

            if ($statusCode === 422) {
                $errors = $message['errors'] ?? [];
            }

            $message = $message['message'];
        }

        parent::__construct($message, $code, $previous);

        if (null !== $type) {
            $this->type = $type;
        }

        if (null !== $statusCode) {
            $this->statusCode = $statusCode;
        }

        if (null !== $errors) {
            $this->errors = $errors;
        }
    }

    public function getType()
    {
        return $this->type;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
