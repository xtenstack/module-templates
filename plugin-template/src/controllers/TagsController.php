<?php
declare(strict_types=1);

namespace XtenPluginTemplate\Controllers;

/**
 * Demo entity for the plugin-template module contract — Tags are
 * deliberately trivial (name + description, nothing else), so this
 * controller is the thing to actually study when copying this template:
 * index/new/edit/view/delete + a "with selected" bulk action, following
 * the RB-03 list-view convention (row actions, New button, bulk ops,
 * search/sort/pagination via App_skeleton\ListView).
 */
class TagsController extends ControllerBase
{
    // Demo module, real feature would pick real roles — admin-only here
    // since a template has no natural non-admin audience of its own,
    // same decision application-template's WidgetsController makes.
    protected function onConstruct()
    {
        $this->allowedRoles = \Roles::idsByNames(['admin']);

        parent::onConstruct();
    }

    public function indexAction()
    {
        $list = \App_skeleton\ListView::paginate(
            $this->request,
            \Tag::class,
            ['name', 'description'],
            ['created' => 'id', 'name' => 'name'],
            [],
            []
        );

        $this->view->tags         = $list['results'];
        $this->view->listState    = $list;
        $this->view->preserveQuery = $list['preserve'];
    }

    public function newAction()
    {
        $this->view->tag = new \Tag();
    }

    public function createAction()
    {
        if (!$this->request->isPost()) {
            return $this->dispatcher->forward(['controller' => 'tags', 'action' => 'index']);
        }

        $name = trim((string) $this->request->getPost('name'));

        if ($name === '') {
            $this->flash->error('Name is required');

            return $this->dispatcher->forward(['controller' => 'tags', 'action' => 'new']);
        }

        $tag              = new \Tag();
        $tag->name        = $name;
        $tag->description = (string) $this->request->getPost('description') ?: null;

        if (!$tag->save()) {
            foreach ($tag->getMessages() as $message) {
                $this->flash->error((string) $message);
            }

            $this->view->tag = $tag;

            return $this->dispatcher->forward(['controller' => 'tags', 'action' => 'new']);
        }

        // Illustrates the intended plugin-composition pattern: this plugin
        // fires a colon-namespaced event on the shared eventsBus (see
        // app/config/services.php) and doesn't know or care who's
        // listening. An application-tier module wanting to react — e.g.
        // auto-tagging something it owns whenever a new Tag appears —
        // would attach a listener for 'tag:created' inside its own
        // Module::registerServices($di), not here. Nothing in this repo
        // actually consumes this event; that's out of scope for a
        // template.
        $this->eventsBus->fire('tag:created', $this, $tag);

        $this->flash->success($tag->name . ' created');

        return $this->response->redirect($this->url->get('tags/tags/view/' . $tag->id));
    }

    public function viewAction($id)
    {
        $tag = \Tag::findFirstById($id);

        if (!$tag) {
            $this->flash->error('Tag was not found');

            return $this->dispatcher->forward(['controller' => 'tags', 'action' => 'index']);
        }

        $this->view->tag = $tag;
    }

    public function editAction($id)
    {
        $tag = \Tag::findFirstById($id);

        if (!$tag) {
            $this->flash->error('Tag was not found');

            return $this->dispatcher->forward(['controller' => 'tags', 'action' => 'index']);
        }

        $this->view->tag = $tag;
    }

    public function updateAction($id)
    {
        $tag = \Tag::findFirstById($id);

        if (!$tag) {
            $this->flash->error('Tag was not found');

            return $this->dispatcher->forward(['controller' => 'tags', 'action' => 'index']);
        }

        if (!$this->request->isPost()) {
            return $this->dispatcher->forward(['controller' => 'tags', 'action' => 'view', 'params' => [$id]]);
        }

        $name = trim((string) $this->request->getPost('name'));

        if ($name === '') {
            $this->flash->error('Name is required');
            $this->view->tag = $tag;

            return $this->dispatcher->forward(['controller' => 'tags', 'action' => 'edit', 'params' => [$id]]);
        }

        $tag->name        = $name;
        $tag->description = (string) $this->request->getPost('description') ?: null;

        if (!$tag->save()) {
            foreach ($tag->getMessages() as $message) {
                $this->flash->error((string) $message);
            }

            $this->view->tag = $tag;

            return $this->dispatcher->forward(['controller' => 'tags', 'action' => 'edit', 'params' => [$id]]);
        }

        $this->flash->success($tag->name . ' updated');

        return $this->response->redirect($this->url->get('tags/tags/view/' . $tag->id));
    }

    public function deleteAction($id)
    {
        $tag = \Tag::findFirstById($id);

        if (!$tag) {
            $this->flash->error('Tag was not found');

            return $this->dispatcher->forward(['controller' => 'tags', 'action' => 'index']);
        }

        if (!$this->request->isPost()) {
            return $this->dispatcher->forward(['controller' => 'tags', 'action' => 'view', 'params' => [$id]]);
        }

        if (!$tag->softDelete()) {
            foreach ($tag->getMessages() as $message) {
                $this->flash->error((string) $message);
            }

            return $this->dispatcher->forward(['controller' => 'tags', 'action' => 'view', 'params' => [$id]]);
        }

        $this->flash->success($tag->name . ' deleted');

        return $this->dispatcher->forward(['controller' => 'tags', 'action' => 'index']);
    }

    /**
     * "With selected" bulk delete from the list view — see the RB-03
     * list-view convention doc. Tag has no field worth batch-editing
     * (name/description are both per-record-meaningful only), so delete
     * is the only bulk op — that's a valid, not a degenerate, case of the
     * convention.
     */
    public function bulkAction()
    {
        if (!$this->request->isPost()) {
            return $this->dispatcher->forward(['controller' => 'tags', 'action' => 'index']);
        }

        $ids = array_filter(array_map('intval', (array) $this->request->getPost('tag_ids', null, [])));

        if (!$ids) {
            $this->flash->error('No tags were selected');

            return $this->dispatcher->forward(['controller' => 'tags', 'action' => 'index']);
        }

        if ((string) $this->request->getPost('bulk_action') !== 'delete') {
            $this->flash->error('Choose an action to apply to the selected tags');

            return $this->dispatcher->forward(['controller' => 'tags', 'action' => 'index']);
        }

        $tags = \Tag::find([
            'conditions' => 'id IN ({ids:array})',
            'bind'       => ['ids' => $ids],
        ]);

        $count = 0;

        foreach ($tags as $tag) {
            if ($tag->softDelete()) {
                $count++;
            }
        }

        $this->flash->success($count . ' tag(s) deleted');

        return $this->dispatcher->forward(['controller' => 'tags', 'action' => 'index']);
    }
}
