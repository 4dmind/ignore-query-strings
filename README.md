# Ignore Query String Parameters

If your website has static caching on, and you drive traffic to it from social media, Google Ads and other sources that add query string parameters to the URL, there is a chance that each time new user visits a page, it will not be served from cache, but will be generated from scratch. This is because the URL with query string parameters is treated as a different URL from the one without query string parameters.

You could always set `'ignore_query_strings' => true`, but that would mean that you would not be able to use query string parameters in your templates.

This addon allows to have both: ignore query string parameters for caching purposes, but still be able to use them in your templates.

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

``` bash
composer require fdmind/igonre-query-strings
```

## How to Use

Addon comes with `ignore-query-strings.php` config file, where you can specify which query string parameters to ignore. By default, it is set to ignore most popular parameters, but you can remove and add your own.
