<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class HttpException extends Exception
{
    private $exception;

    public function __construct($message = "", $code = 0, Exception $exception = null)
    {
        parent::__construct($message, $code);
        $this->exception = $exception;
    }

    public function render()
    {
        $response = [
            'code' => $this->code,
            'msg' => $this->message,
        ];
        // 非生产环境下输出异常
        if ($this->exception && config('app.env') != 'production') {
            $response['error'] = [
                'error_msg' => $this->exception->getMessage(),
                'error_code' => $this->exception->getCode()
            ];
        }
        return response()->json($response, 200);
    }
}
