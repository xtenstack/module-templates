<?php
declare(strict_types=1);

namespace XtenPluginTemplate;

use Phalcon\Di\DiInterface;
use Phalcon\Autoload\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\ModuleDefinitionInterface;

/**
 * Plugin-tier module (see MODULE-SPEC.md/module.json) — routed under
 * /tags/... by app/config/routes.php once enabled. As of 2026-08-12,
 * ModuleManager::ROUTABLE_TIERS treats plugin-tier identically to
 * application-tier for routing/menu purposes (see this template's own
 * README for the honest parity note) — so this class looks exactly like
 * application-template's Module.php. Everything else this module needs
 * (db, session, auth, flash, moduleManager, audit, eventsBus...) comes
 * from the shared DI services app/config/services*.php already register
 * globally; this only needs to add its own controllers/models to the
 * autoloader and point the view at its own templates.
 *
 * An application-tier module that wants to react to this plugin's
 * tag:created event (see TagsController::createAction()) attaches its
 * own listener here — on *its own* Module::registerServices($di), not
 * this one — since this plugin doesn't know or care who's listening.
 */
class Module implements ModuleDefinitionInterface
{
    public function registerAutoloaders(?DiInterface $di = null)
    {
        $loader = new Loader();

        $loader->setNamespaces([
            'XtenPluginTemplate\Controllers' => __DIR__ . '/controllers/',
        ]);

        // Tag is a bare/global class (see src/models/), matching how
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
