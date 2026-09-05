# plugin-template

A blank/template **plugin-tier** module for app_skeleton's module system,
demonstrating the full module contract described in `docs/MODULE-SPEC.md`
(app_skeleton repo). Its demo entity, **Tags** (a name + description and
nothing else meaningful), exists purely to give every layer of the
contract something real to attach to — this is not a feature to install,
it's scaffolding to copy.

## Application-tier vs. plugin-tier: the honest current state

As of 2026-08-12, application-tier and plugin-tier modules are
**code-mechanically identical** in what `ModuleManager` actually does with
them — both get a real route namespace (`registeredPhalconModules()`) and
a menu contribution (`mergedMenu()`); see `ROUTABLE_TIERS` in
`app/common/library/ModuleManager.php`, which lists both tiers together.
This template's own `src/`, `module.json`, `menu.php` — every file here —
is therefore line-for-line the same shape as `application-template/`,
just with `tier: "plugin"` and a different demo entity.

The only *designed* distinction between the tiers — plugin-tier modules
never appearing in a top-nav application switcher, reachable only via the
left-nav route — isn't implemented in code yet. That switcher UI doesn't
exist at all today; see `docs/module-system-design-brief.md`'s "v1.2
direction" for the planned shape. This template deliberately does not
invent an extra restriction to simulate a difference that isn't real yet
— don't add one when copying this as a starting point either, until that
UI actually ships.

What *is* real today: the conceptual intent. A plugin-tier module is
meant to be small and portable, composed into whatever application-tier
module wants it, rather than anchoring what an instance *is*. This
template's `tag:created` event-bus fire (see below) is how that
composition is meant to happen once there's an application-tier module
on the other end of it.

## What's here

```
composer.json          # standard Composer package metadata
module.json             # the manifest ModuleManager reads — key: "tags", tier: "plugin"
src/
  Module.php             # ModuleDefinitionInterface: autoloaders + own view service
  controllers/
    ControllerBase.php    # auth/CSRF/role gate, mirrors backend's own ControllerBase
    IndexController.php    # bare /tags landing spot + 404/500 forward targets
    TagsController.php      # index/new/edit/view/delete + bulk, follows RB-03
  models/
    Tag.php                  # SoftDeletes + keepSnapshots(true)
migrations/
  postgresql/
    001_tags.sql              # id, name, description, deleted_at, created_at, updated_at
views/
  tags/
    index.phtml, new.phtml, edit.phtml, view.phtml
menu.php                 # one sidebar entry: Tags -> tags
```

## The plugin-composition pattern: `tag:created`

After a Tag is created, `TagsController::createAction()` fires a
colon-namespaced `tag:created` event on the shared `eventsBus` service
(`app/config/services.php`), matching the existing
`payment:completed`/`user:created` convention. This plugin doesn't know
or care who's listening — an application-tier module that wants to react
(e.g. auto-tagging something it owns whenever a new Tag appears) attaches
its own listener for `tag:created` inside *its own*
`Module::registerServices($di)`, not this plugin's. No such consumer is
built here — building one is out of scope for a template; see the
`fire()` call itself for the illustrating comment.

## Using this as a starting point for a real module

1. Copy this whole directory to a new location (its own repo, or a new
   folder in yours), rename it away from `plugin-template`.
2. Rename the Composer package in `composer.json` (`name`, PSR-4 prefix)
   and the PHP namespace throughout `src/` to match — this template uses
   `XtenPluginTemplate`.
3. Change `module.json`'s `key` (and `code`, `className`) to your real
   module's identifier. `key` must be unique across every module
   installed on an instance — it drives `module_registry.module_key` and
   the `/<key>/...` route namespace. Keep `tier: "plugin"` if your module
   is meant to be composed into other application-tier modules rather
   than anchor an instance on its own.
4. Replace `Tag`/`Tags` throughout (`src/models/Tag.php`,
   `src/controllers/TagsController.php`, `views/tags/`, `menu.php`) with
   your real entity — keep the same shape (`ControllerBase` auth/CSRF
   gate, `SoftDeletes` on the model, RB-03's list-view conventions on the
   index view) rather than re-deriving it.
5. Update `migrations/postgresql/001_*.sql` for your entity's real
   columns, and add further numbered migrations as the schema evolves.
6. Pick real `$allowedRoles` for your controller(s) — don't ship with
   the demo's admin-only default unless that's actually correct for your
   module.
7. If your module fires or listens for its own events, follow the same
   colon-namespaced convention (`yourmodule:event`) and attach listeners
   in `Module::registerServices($di)`, never by reaching directly into
   another module's tables (see MODULE-SPEC.md's Isolation section).

## What this deliberately is not

Not a feature showcase — Tags have no relationships, no bulk-editable
fields, no API surface beyond the one illustrative event fire. Real
modules will need more than this template demonstrates (see
`requirements-module` in this project's private `internal` repo for a
fuller worked example of the same contract) — start here for the shape,
not for feature coverage.

Fields marked `(planned)` in `docs/MODULE-SPEC.md` (`icon`, `license`,
`dependsOn`) are intentionally absent from `module.json` — they aren't
read by `ModuleManager` yet.
