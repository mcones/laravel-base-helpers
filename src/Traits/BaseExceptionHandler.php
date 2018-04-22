<?php

namespace Mcones\LaravelBaseHelpers\Traits;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException as AuthorizationException2;
use Mcones\LaravelBaseHelpers\Exceptions\BaseException;

trait BaseExceptionHandler
{

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param Request $request
     * @param Exception $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForException(Request $request, Exception $e)
    {
        switch(true) {
            case $this->isModelNotFoundException($e):
                $retval = $this->modelNotFound($e);
                break;

            case $this->isAuthenticationException($e):

                $retval= $this->notAuthenticated();
                break;

            case $this->isBaseException($e):

                $retval= $this->generateBadRequestResponse($e);
                break;

            case $this->isValidationException($e);
                $retval= $this->requestNotValid($e,$request);
                break;
            default:
                $retval = $this->generalException($e);
        }

        return $retval;
    }

    protected function generateBadRequestResponse(BaseException $e){

        $payload=['key'=>$e->key, 'message' => $e->message];

        if($e->additional_data){
            $payload['additional_data']=$e->additional_data;
        }

        $statusCode=$e->statusCode;

        return $this->jsonResponse($payload,$statusCode);
    }

    /**
     * Returns json response for generic bad request.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function badRequest($message='Bad request', $statusCode=400)
    {
        return $this->jsonResponse(['error' => $message], $statusCode);
    }


    protected function generalException($e){

       $message=[ 'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine()
       ];

        return $this->jsonResponse($message,500);
    }

    /**
     * Returns json response for Eloquent model not found exception.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function modelNotFound(Exception $exception, $statusCode=404)
    {

        $message='Record not found';
        if(isset($exception)){
          $message= $exception->getModel().' under Id '.implode(",",$exception->getIds()).' not found';
        }

        return $this->jsonResponse(['key' => 'ENTITY_NOT_FOUND', 'message' => $message], $statusCode);
    }

    protected function notAuthenticated(){
        return $this->jsonResponse(['key'=> 'NOT_AUTHENTICATED','message'=>'Authentication not valid'],401);
    }

    protected function requestNotValid(Exception $exception,Request $request){

        if ($exception->response) {
            return $exception->response;
        }

        return $this->jsonResponse([
            'message' => $exception->getMessage(),
            'errors' => $exception->errors(),
        ], 400);

    }
    /**
     * Returns json response.
     *
     * @param array|null $payload
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonResponse(array $payload=null, $statusCode=404)
    {
        $payload = $payload ?: [];

        return response()->json($payload, $statusCode);
    }



    protected function isBaseException(Exception $e){
        return $e instanceof BaseException;
    }

    protected function isModelNotFoundException(Exception $e)
    {
        return $e instanceof ModelNotFoundException;
    }

    protected function isAuthenticationException(Exception $e){
        return $e instanceof AuthenticationException || $e instanceof AuthorizationException2;
    }

    protected function isValidationException(Exception $e){
        return $e instanceof ValidationException;
    }

}