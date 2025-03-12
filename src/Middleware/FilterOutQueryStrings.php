<?php

namespace Fdmind\IgnoreQueryStrings\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\InputBag;
use Closure;

class FilterOutQueryStrings
{
    private $cachingStrategy;
    private $isStaticCachingOn;
    private $queryStringParameters;
    private $excludedPaths;
    private $mode;


    public function __construct()
    {
        $this->cachingStrategy = config('statamic.static_caching.strategy');
        $this->isStaticCachingOn = $this->cachingStrategy === 'half' || $this->cachingStrategy === 'full';
        $this->mode = config('ignore-query-strings.mode');

        $this->defaultQueryStringParameters = [
            'page',
            'q',
        ];
        $this->queryStringParameters = config('ignore-query-strings.parameters');


        $glideRoute = trim(config('statamic.assets.image_manipulation.route'), '/');
        $cpRoute = trim(config('statamic.cp.route'), '/');

        $this->defaultExcludedPaths = [
          $cpRoute . '/*',            // Control Panel (from config)
          $glideRoute . '/*',         // Glide image manipulation (from config)
          'graphql',                  // GraphQL endpoint
          'graphql/*',                // GraphQL sub-routes
          'api/*',                    // API endpoints
        ];

        $this->excludedPaths = array_merge($this->defaultExcludedPaths, config('ignore-query-strings.excluded_paths'));
    }

    public function handle(Request $request, Closure $next)
    {

        if (!$this->isStaticCachingOn) {
            return $next($request);
        }

        foreach($this->excludedPaths as $path) {
          if($request->is($path)) return $next($request);
        }

        $alteredRequest = $this->removeRequestQueryParams($request);
        return $next($alteredRequest);
    }

    private function removeRequestQueryParams(Request $request): Request
    {
        $requestQueryParams = $request->query();
        $requestUri = $request->getUri();

        foreach ($requestQueryParams as $key => $value) {

            if (in_array($key, $this->defaultQueryStringParameters)) {
                continue;
            }


            $regexPattern = "/&?" . preg_quote($key) . "=(.*?(?=[&])|.*)/";

            if ($this->mode == 'deny' && in_array($key, $this->queryStringParameters)) {

                unset($requestQueryParams[$key]);
                $requestUri = preg_replace($regexPattern, '', $requestUri);

            } elseif($this->mode == 'allow' && !in_array($key, $this->queryStringParameters)) {

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
