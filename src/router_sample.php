<?php declare(strict_types=1);
/**
 * PHPCore - Router Sample
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 * @link https://manual.phpcore.org/router
 */

// -------------------------------------------------------------------------------------------------

// Bootstrap
include getenv('PHPCORE_BOOTSTRAP');

// Example: Declare routes here
$routes = [
    [
        'Expression' => "/^article/:article_id",
        'Controller' => "\App\Controllers\Article",
    ],
    [
        'Expression' => "/^articles",
        'Controller' => "\App\Controllers\Article",
    ],
    [
        'Expression' => "/^current_articles",
        'Redirect'   => "permanent",
        'NewRoute'   => "/^articles.@format?view=current",
    ],
    [
        'Expression' => "/^my_articles",
        'Controller' => "\App\Controllers\Article",
        'Params'     => [ 'owner_id' => @user_id ]
    ],
];

// Example: Getting from file
$path = 'data/routes.json';
$json = file_get_contents($path);
$routes = json_decode($json);

// Example: Getting from databse
$table = 'tblRoutes';
$order_by = ['order'];
$routes = database()->getRecords($table, null, $order_by);

// Execute router
Route::addRoutes($routes);
$router = new \PHPCore\Route();
$router->execute();

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
