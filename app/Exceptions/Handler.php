<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {

        if($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException){
          return response()->json(array('status' => 'failed, url is not found'));
        }
        if ($e instanceof \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException){
          return response()->json(array('status' => 'failed, file is not found'));
        }
        // return response()->json(array('status' => 'failed, check  your connection'));
        return parent::render($request, $e);
    }
}
