<?php

namespace Fdmind\IgnoreQueryStrings\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\InputBag;
use Closure;

class FilterOutQueryStrings
{
    private $cachingStrategy;
    private $isStaticCachingOn;
    private $parametersToIgnore;
    private $mode;

    public function __construct()
    {
        $this->cachingStrategy = config('statamic.static_caching.strategy');
        $this->isStaticCachingOn = $this->cachingStrategy === 'half' || $this->cachingStrategy === 'full';
        $this->mode = config('ignore-query-strings.mode');
        $this->parametersToIgnore = config('ignore-query-strings.parameters');
    }

    public function handle(Request $request, Closure $next)
    {
        if (!$this->isStaticCachingOn) {
            return $next($request);
        }

        if($request->is('cp/*') || $request->is('api/*') || $request->is('graphql/*')) {
            return $next($request);
        }

        $alteredRequest = $this->removeRequestQueryParams($request);
        return $next($alteredRequest);
    }

    private function removeRequestQueryParams(Request $request): Request
    {
        $requestQueryParams = $request->query();
        $requestUri = $request->getUri();

        foreach ($requestQueryParams as $key => $value) {
            $regexPattern = "/&?" . $key . "=(.*?(?=[&])|.*)/";
            if ($this->mode == 'deny' && in_array($key, $this->parametersToIgnore)) {
                unset($requestQueryParams[$key]);
                $requestUri = preg_replace($regexPattern, '', $requestUri);
            } elseif($this->mode == 'allow' && !in_array($key, $this->parametersToIgnore)) {
                unset($requestQueryParams[$key]);
                $requestUri = preg_replace($regexPattern, '', $requestUri);
            }
        }

        $request->query = new InputBag($requestQueryParams);
        $request->server->set('QUERY_STRING', http_build_query($requestQueryParams));
        $request->server->set('REQUEST_URI', $requestUri);

        return $request;
    }

}
