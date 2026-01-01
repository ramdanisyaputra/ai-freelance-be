<?php

namespace App\Traits;

trait ResponseTrait
{
    public function successResponsePaginated($data, $code = 200) 
    {
        return response()->json($data, $code);
    }

    public function successResponse($data, $code = 200) 
    {
        return response()->json(['data' => $data], $code);
    }

    public function successResponseMessage($message, $code = 200) 
    {
        return response()->json(['message' => $message], $code);
    }

    public function errorResponse($message, $code) 
    {
        return response()->json(['message' => $message], $code);
    }

    public function validation($validator) 
    {
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }
    }

    public function notFoundResponse($message) 
    {
        return $this->errorResponse($message, 404);
    }

    public function unauthorizedResponse($message) 
    {
        return $this->errorResponse($message, 403);
    }

    public function forbiddenResponse($message) 
    {
        return $this->errorResponse($message, 403);
    }

    public function internalServerErrorResponse($message) 
    {
        return $this->errorResponse($message, 500);
    }
}
