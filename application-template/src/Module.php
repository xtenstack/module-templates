<?php
declare(strict_types=1);

namespace XtenApplicationTemplate;

use Phalcon\Di\DiInterface;
use Phalcon\Autoload\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\ModuleDefinitionInterface;

/**
 * Application-tier module (see MODULE-SPEC.md/module.json) — routed under
 * /widgets/... by app/config/routes.php once enabled. Everything else it
 * needs (db, session, auth, flash, moduleManager, audit...) comes from the
 * shared DI services app/config/services*.php already register globally;
 * this only needs to add its own controllers/models to the autoloader and
 * point the view at its own templates. Pattern-matched from
 * xtenstack/requirements-module's Module.php — copy this file as-is when
 * starting a real module from this template.
 */
class Module implements ModuleDefinitionInterface
{
    public function registerAutoloaders(?DiInterface $di = null)
    {
        $loader = new Loader();

        $loader->setNamespaces([
            'XtenApplicationTemplate\Controllers' => __DIR__ . '/controllers/',
        ]);

        // Widget is a bare/global class (see src/models/), matching how
        // app/config/loader.php loads the built-in modules' own models —
        // can't be matched by setNamespaces().
        $loader->setDirectories([
            __DIR__ . '/models/',
        ]);

        $loader->register();
    }

    public function registerServices(DiInterface $di)
    {
        $di['view'] = function () {
            $view = new View();
            $view->setViewsDir(__DIR__ . '/../views/');

            $view->registerEngines([
                '.volt'  => 'voltShared',
                '.phtml' => PhpEngine::class,
            ]);

            return $view;
        };
    }
}
