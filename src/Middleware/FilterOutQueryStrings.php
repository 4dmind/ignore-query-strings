<?php

namespace Fdmind\IgnoreQueryStrings\Middleware;

use Illuminate\Http\Request;
use Statamic\Tags\In;
use Symfony\Component\HttpFoundation\InputBag;
use Closure;

class FilterOutQueryStrings
{
    private $cachingStrategy;
    private $isStaticCachingOn;
    private $parametersToIgnore;

    public function __construct()
    {
        $this->cachingStrategy = config('statamic.static_caching.strategy');
        $this->isStaticCachingOn = !empty($cachingStrategy) && ($cachingStrategy == 'half' || $cachingStrategy == 'full');
        $this->parametersToIgnore = config('ignore-query-strings.parameters');

    }

    public function handle(Request $request, Closure $next)
    {
//        if (!$this->isStaticCachingOn) {
//            return $next($request);
//        }

        $alteredRequest = $this->removeRequestQueryParams($request);
        return $next($alteredRequest);
    }

    private function removeRequestQueryParams(Request $request): Request
    {
        $requestQueryParams = $request->query();
        $requestUri = $request->getUri();

        foreach ($requestQueryParams as $key => $value) {
            if (in_array($key, $this->parametersToIgnore)) {
                unset($requestQueryParams[$key]);
                $regexPattern = "/&?" . $key . "=(.*?(?=[&])|.*)/";
                $matches = [];
                $match = preg_match($regexPattern, $requestUri, $matches);
                $requestUri = preg_replace($regexPattern, '', $requestUri);
            }
        }

        $request->query = new InputBag($requestQueryParams);
        $request->server->set('QUERY_STRING', http_build_query($requestQueryParams));
        $request->server->set('REQUEST_URI', $requestUri);

        return $request;
    }

}
