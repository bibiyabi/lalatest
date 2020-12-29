<?php


namespace App\Exceptions;



use Illuminate\Support\Facades\Log;

class CustomException extends \Exception
{
    private $error_code;
    private $error_message;
    private $error_url;
    private $parameter;

    public function __construct($error, $parameter)
    {
        $this->error_code = $error['code'];
        $this->error_message = $error['message'];
        $this->error_url = \Route::getCurrentRoute()->uri;
        $this->parameter = $parameter;
    }

    public function report()
    {
        Log::info(json_encode([
            'error_code'    => $this->error_code,
            'error_message' => $this->error_message,
            'error_line'    => $this->getTrace()[0]['line'],
            'Api_url'       => $this->getTrace()[0]['file'],
        ]));

    }

    public function render()
    {
        if (env('APP_DEBUG')){
            return ['error_code'    => $this->error_code,
                    'error_message' => $this->error_message,
                    'error_line'    => $this->getTrace()[0]['line'],
                    'error_at'      => $this->getTrace()[0]['file'],
            ];
        }else{
            return ['error_code'    => $this->error_code,
                    'error_message' =>$this->error_message,
            ];
        }

    }
}
