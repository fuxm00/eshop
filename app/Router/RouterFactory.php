<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;

final class RouterFactory {
    use Nette\StaticClass;

    public static function createRouter(): RouteList {
        $adminRouter = new RouteList('Admin');
        $adminRouter->addRoute('admin/<presenter=Dashboard>/<action=default>[/<id>]');

        $frontRouter = new RouteList('Front');
        $frontRouter->addRoute('sitemap.xml', 'Homepage:sitemap');
        $frontRouter->addRoute('produkty/<url>', 'Product:show');
        $frontRouter->addRoute('kosik', 'Cart:default');
        $frontRouter->addRoute('<presenter=Product>/<action=list>[/<id>]');
        $router = new RouteList();
        $router->add($adminRouter);
        $router->add($frontRouter);
        return $router;
    }
}