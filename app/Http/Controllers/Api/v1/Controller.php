<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Continhas API",
    version: "1.0"
)]
#[OA\SecurityScheme(
    securityScheme: 'Bearer',
    scheme: 'Bearer',
    type: 'http',
    description: "hehehe hahaha",
    bearerFormat: "JWT"
)]
#[OA\OpenApi(security: [["Bearer" => []]])]
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
