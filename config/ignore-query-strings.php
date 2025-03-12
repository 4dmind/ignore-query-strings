<?php

return [
  /*
   * By default all Statamics internal params are allowed to pass through (CP, Glide, GraphQL, pagination) regardless of the mode.
   * If you need additional params to work, set mode to allow and add them to the array
   */

  //'mode' => 'deny', // DENY only query string params listed below
  'mode' => 'allow', // only ALLOW query string params listed below

  'parameters' => [

  ],



  /*
   * Addon be default allows to pass through all query string params
   * for following URI paths:
   * 1) config('statamic.assets.image_manipulation.route') - to make sure that GLIDE will work (you should still cache images though)
   * 2) config('statamic.cp.route')
   * 3) /graphql
   * 4) /api
   *
   * Below you can configure additional URI paths that you want to keep all QSPs for.
  */
  'excluded_paths' => [ // allow all query string params for URI paths starting with:
//    '_download',
  ],
];
