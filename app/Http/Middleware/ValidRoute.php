<?php

namespace App\Http\Middleware;

use Closure;

class ValidRoute {
    
    protected $params = [];
    
    protected $validator = [];
    
    protected $appendParams = [];
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $routeParams = $request->route()->parameters();
        
        foreach($this->params as $param => $message) {
            if (!isset($routeParams[$param])) {
                if (!$message['optional']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        $param => ["{$param} must be present."]
                     ]);
                } 
                
                continue;                
            }
            
            if (!isset($this->validator[$param])) {
                throw new \Exception("{$param} must be set in validator");
            }
            
            if (!$this->validator[$param]($routeParams[$param])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    $param => [$message['message']]
                ]);
            }
            
            $request[$this->appendParams[$param]] = $request->route()->parameters()[$param];
        }
        
        return $next($request);
    }
    
}
