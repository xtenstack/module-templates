# application-template

A blank/template **application-tier** module for app_skeleton's module
system, demonstrating the full module contract described in
`docs/MODULE-SPEC.md` (app_skeleton repo). Its demo entity, **Widgets**
(a name + description and nothing else meaningful), exists purely to give
every layer of the contract something real to attach to — this is not a
feature to install, it's scaffolding to copy.

## What's here

```
composer.json          # standard Composer package metadata
module.json             # the manifest ModuleManager reads — key: "widgets", tier: "application"
src/
  Module.php             # ModuleDefinitionInterface: autoloaders + own view service
  controllers/
    ControllerBase.php    # auth/CSRF/role gate, mirrors backend's own ControllerBase
    IndexController.php    # bare /widgets landing spot + 404/500 forward targets
    WidgetsController.php   # index/new/edit/view/delete + bulk, follows RB-03
  models/
    Widget.php              # SoftDeletes + keepSnapshots(true)
migrations/
  postgresql/
    001_widgets.sql          # id, name, description, deleted_at, created_at, updated_at
views/
  widgets/
    index.phtml, new.phtml, edit.phtml, view.phtml
menu.php                 # one sidebar entry: Widgets -> widgets
```

## Using this as a starting point for a real module

1. Copy this whole directory to a new location (its own repo, or a new
   folder in yours), rename it away from `application-template`.
2. Rename the Composer package in `composer.json` (`name`, PSR-4 prefix)
   and the PHP namespace throughout `src/` to match — this template uses
   `XtenApplicationTemplate`.
3. Change `module.json`'s `key` (and `code`, `className`) to your real
   module's identifier. `key` must be unique across every module
   installed on an instance — it drives `module_registry.module_key` and
   the `/<key>/...` route namespace.
4. Replace `Widget`/`Widgets` throughout (`src/models/Widget.php`,
   `src/controllers/WidgetsController.php`, `views/widgets/`,
   `menu.php`) with your real entity — keep the same shape
   (`ControllerBase` auth/CSRF gate, `SoftDeletes` on the model, RB-03's
   list-view conventions on the index view) rather than re-deriving it.
5. Update `migrations/postgresql/001_*.sql` for your entity's real
   columns, and add further numbered migrations as the schema evolves.
6. Pick real `$allowedRoles` for your controller(s) — don't ship with
   the demo's admin-only default unless that's actually correct for your
   module.

## What this deliberately is not

Not a feature showcase — Widgets have no relationships, no bulk-editable
fields, no API surface, no event-bus usage. Real modules will need more
than this template demonstrates (see `requirements-module` in this
project's private `internal` repo for a fuller worked example of the
same contract) — start here for the shape, not for feature coverage.

Fields marked `(planned)` in `docs/MODULE-SPEC.md` (`icon`, `license`,
`dependsOn`) are intentionally absent from `module.json` — they aren't
read by `ModuleManager` yet.
