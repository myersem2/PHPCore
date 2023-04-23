<?php declare(strict_types=1);
/**
 * PHPCore - Route
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

namespace PHPCore;

// -------------------------------------------------------------------------------------------------

/**
 * Route Class
 */
final class Route
{
    use Core;

    /**
     * Route ID
     *
     * // TODO: see if this is needed for nesting
     *
     * @var array
     */
    private static int $Id = 0;

    /**
     * Route passed data
     *
     * @var array
     */
    public readonly array $Data;

    // ---------------------------------------------------------------------

    /**
     * Active Route Count
     *
     * @var array
     */
    private static int $RouteCount = 0;

    /**
     * Routes
     *
     * @var array
     */
    private static array $Routes = [/*
        // NOTE: routes are processed in order and stop and first match
        [
            // Required
            'Expression'  => (string) regular expression to match (i.e. "/^article/:article_id")
            'Controller'  => (string) Controller name space (i.e. "\App\Controllers\Article")

            // Optional
            'Redirect' => (string) Redirect ("temporary", "permanent", "invisiable"), requires "NewRoute"
                            "temporary" => 307 Temporary Redirect
                            "permanent" => 301 Moved Permanently
                            "invisiable" => xxx respons the same as correct path (seamless)
            'NewRoute' => (boolean) new route with param tranposed
                          (i.e. "/articles/view.@format?article_id=:article_id")
            'Params' => (array) array of static params for route (i.e. [ 'owner_id' => @user_id ]
        ],
    */];

    // ---------------------------------------------------------------------

    // TODO: Document
    public function __construct(?string $request_uri = null, array $data = null)
    {
        self::$RouteCount++;

        $this->Data = $data;
        $this->Id = self::$RouteCount;

        // TODO: process string and loop throught self::$Routes to determine preg_match()
        //       if NOT found use DefaultRoute defined in phpcore.ini
    }

    // TODO: document
    public function execute(?string $method = null):void
    {
        $method = $method ?? strtolower($_SERVER['REQUEST_METHOD']);
        // TODO: call controller method
    }

    // TODO: document
    public function param(?string $key = null): mixed
    {
        // TODO: get para from route (i.e. "/^article/:article_id" ..... "/article/12345.json" .... $this->param('article_id') // 1235
    }

    // ---------------------------------------------------------------------

    // TODO: document
    public static function addRoute(array $route):void
    {
        // TODO: add route to self::$Routes
    }

    // TODO: document
    public static function addRoutes(array $routes):void
    {
        // TODO: add routes to self::$Routes
    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
