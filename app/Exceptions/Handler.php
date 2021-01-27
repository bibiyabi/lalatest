<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use App\Constants\Payments\ResponseCode;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        NotifyException::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof GatewayNotFountException) {
            return RB::asError(ResponseCode::GATEWAY_NOT_FOUND)->withMessage($e->getMessage())->build();
        } elseif ($e instanceof \PDOException) {
            return RB::asError(ResponseCode::DATABASE_FAILED)->withMessage($e->getMessage())->build();
        } elseif ($e instanceof ValidationException) {
            return RB::error(ResponseCode::ERROR_PARAMETERS);
        }

        return parent::render($request, $e); // TODO: Change the autogenerated stub
    }

    public function report(Throwable $e)
    {
        return parent::report($e); // TODO: Change the autogenerated stub
    }
}
