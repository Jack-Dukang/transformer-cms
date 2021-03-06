<?php

namespace App\Exceptions;

use BadMethodCallException;
use Exception;
use HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
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
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof BadMethodCallException){
            return response()->view('errors.' . 404, ['message'=>'NOT FOUND'], 404);
        }
        /* 错误页面 */
        if ($exception instanceof HttpException) {
            $code = $exception->getStatusCode()??404;

            if (view()->exists('errors.' . $code)) {
                $message  = $exception->getMessage();
                return response()->view('errors.' . $exception->getStatusCode(), ['message'=>$message], $exception->getStatusCode());
            }
        }

        return parent::render($request, $exception);
    }
}
