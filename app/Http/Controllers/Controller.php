<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller
{
    public function success(
        array $data = [],
        ?string $message = null,
        int $httpCode = Response::HTTP_OK
    ): JsonResponse
    {
        $response = [
            "status" => true,
        ];

        if($data){
            $response["data"] = $data;
        }

        if($message){
            $response["message"] = $message;
        }

        return \response()
            ->json(
                $response,
                $httpCode
            );
    }

    public function error(
        ?string $message = null,
        array $data = [],
        int $httpCode = Response::HTTP_BAD_REQUEST
    ): JsonResponse
    {
        $response = [
            "status" => false,
        ];

        if($data){
            $response["data"] = $data;
        }

        if($message){
            $response["message"] = $message;
        }

        return \response()
            ->json(
                $response,
                $httpCode
            );
    }
}
