<?php


namespace App\Http\Middleware;


use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseAddMeta
{
    public function handle($request, \Closure $next)
    {
        $response = $next($request);

        $meta = [
            'app' => env('APP_NAME'),
            'env' => env('APP_ENV'),
            'version' => env('APP_VERSION', 'unspecified'),
        ];

        if ($response instanceof JsonResponse) {
            $json = json_decode($response->getContent());
            $json['meta'] = $meta;
            $response->setJson(json_encode($json));
        }

        return $response;
    }

}
