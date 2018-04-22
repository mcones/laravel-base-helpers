<?php

namespace Mcones\LaravelBaseHelpers\Traits;

use Illuminate\Http\Request;

trait BaseApiController
{
    protected function paginate(Request $request,$query){
        $per_page = $request->input('per_page', 10);
        $result=$query->paginate($per_page);
        $result->appends($request->query());
        return $this->Ok($result);
    }

    protected function Ok($payload){
        return $this->jsonResponse($payload);
    }

    protected function created($payload){
        return $this->jsonResponse($payload,201);
    }

    protected function updated($payload){
        return $this->jsonResponse($payload);
    }

    protected function deleted(){
        return $this->jsonResponse(null,204);
    }

    protected function notProcessable($key='BAD_REQUEST',$message='Operation not processable'){
        $payload=['key'=>$key,'message' => $message];
        return $this->jsonResponse($payload,422);
    }

    protected function jsonResponse($payload=null, $statusCode=200)
    {
        $payload = $payload ?: [];

        return response()->json($payload, $statusCode);
    }

    protected function isApiCall(Request $request)
    {
        return strpos($request->getUri(), '/api/') !== false;
    }

}