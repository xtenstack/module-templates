<?php
declare(strict_types=1);

namespace XtenApplicationTemplate\Controllers;

/**
 * Demo entity for the application-template module contract — Widgets are
 * deliberately trivial (name + description, nothing else), so this
 * controller is the thing to actually study when copying this template:
 * index/new/edit/view/delete + a "with selected" bulk action, following
 * the RB-03 list-view convention (row actions, New button, bulk ops,
 * search/sort/pagination via App_skeleton\ListView).
 */
class WidgetsController extends ControllerBase
{
    // Demo module, real feature would pick real roles — admin-only here
    // since a template has no natural non-admin audience of its own.
    protected function onConstruct()
    {
        $this->allowedRoles = \Roles::idsByNames(['admin']);

        parent::onConstruct();
    }

    public function indexAction()
    {
        $list = \App_skeleton\ListView::paginate(
            $this->request,
            \Widget::class,
            ['name', 'description'],
            ['created' => 'id', 'name' => 'name'],
            [],
            []
        );

        $this->view->widgets      = $list['results'];
        $this->view->listState    = $list;
        $this->view->preserveQuery = $list['preserve'];
    }

    public function newAction()
    {
        $this->view->widget = new \Widget();
    }

    public function createAction()
    {
        if (!$this->request->isPost()) {
            return $this->dispatcher->forward(['controller' => 'widgets', 'action' => 'index']);
        }

        $name = trim((string) $this->request->getPost('name'));

        if ($name === '') {
            $this->flash->error('Name is required');

            return $this->dispatcher->forward(['controller' => 'widgets', 'action' => 'new']);
        }

        $widget              = new \Widget();
        $widget->name        = $name;
        $widget->description = (string) $this->request->getPost('description') ?: null;

        if (!$widget->save()) {
            foreach ($widget->getMessages() as $message) {
                $this->flash->error((string) $message);
            }

            $this->view->widget = $widget;

            return $this->dispatcher->forward(['controller' => 'widgets', 'action' => 'new']);
        }

        $this->flash->success($widget->name . ' created');

        return $this->response->redirect($this->url->get('widgets/widgets/view/' . $widget->id));
    }

    public function viewAction($id)
    {
        $widget = \Widget::findFirstById($id);

        if (!$widget) {
            $this->flash->error('Widget was not found');

            return $this->dispatcher->forward(['controller' => 'widgets', 'action' => 'index']);
        }

        $this->view->widget = $widget;
    }

    public function editAction($id)
    {
        $widget = \Widget::findFirstById($id);

        if (!$widget) {
            $this->flash->error('Widget was not found');

            return $this->dispatcher->forward(['controller' => 'widgets', 'action' => 'index']);
        }

        $this->view->widget = $widget;
    }

    public function updateAction($id)
    {
        $widget = \Widget::findFirstById($id);

        if (!$widget) {
            $this->flash->error('Widget was not found');

            return $this->dispatcher->forward(['controller' => 'widgets', 'action' => 'index']);
        }

        if (!$this->request->isPost()) {
            return $this->dispatcher->forward(['controller' => 'widgets', 'action' => 'view', 'params' => [$id]]);
        }

        $name = trim((string) $this->request->getPost('name'));

        if ($name === '') {
            $this->flash->error('Name is required');
            $this->view->widget = $widget;

            return $this->dispatcher->forward(['controller' => 'widgets', 'action' => 'edit', 'params' => [$id]]);
        }

        $widget->name        = $name;
        $widget->description = (string) $this->request->getPost('description') ?: null;

        if (!$widget->save()) {
            foreach ($widget->getMessages() as $message) {
                $this->flash->error((string) $message);
            }

            $this->view->widget = $widget;

            return $this->dispatcher->forward(['controller' => 'widgets', 'action' => 'edit', 'params' => [$id]]);
        }

        $this->flash->success($widget->name . ' updated');

        return $this->response->redirect($this->url->get('widgets/widgets/view/' . $widget->id));
    }

    public function deleteAction($id)
    {
        $widget = \Widget::findFirstById($id);

        if (!$widget) {
            $this->flash->error('Widget was not found');

            return $this->dispatcher->forward(['controller' => 'widgets', 'action' => 'index']);
        }

        if (!$this->request->isPost()) {
            return $this->dispatcher->forward(['controller' => 'widgets', 'action' => 'view', 'params' => [$id]]);
        }

        if (!$widget->softDelete()) {
            foreach ($widget->getMessages() as $message) {
                $this->flash->error((string) $message);
            }

            return $this->dispatcher->forward(['controller' => 'widgets', 'action' => 'view', 'params' => [$id]]);
        }

        $this->flash->success($widget->name . ' deleted');

        return $this->dispatcher->forward(['controller' => 'widgets', 'action' => 'index']);
    }

    /**
     * "With selected" bulk delete from the list view — see the RB-03
     * list-view convention doc. Widget has no field worth batch-editing
     * (name/description are both per-record-meaningful only), so delete
     * is the only bulk op — that's a valid, not a degenerate, case of the
     * convention.
     */
    public function bulkAction()
    {
        if (!$this->request->isPost()) {
            return $this->dispatcher->forward(['controller' => 'widgets', 'action' => 'index']);
        }

        $ids = array_filter(array_map('intval', (array) $this->request->getPost('widget_ids', null, [])));

        if (!$ids) {
            $this->flash->error('No widgets were selected');

            return $this->dispatcher->forward(['controller' => 'widgets', 'action' => 'index']);
        }

        if ((string) $this->request->getPost('bulk_action') !== 'delete') {
            $this->flash->error('Choose an action to apply to the selected widgets');

            return $this->dispatcher->forward(['controller' => 'widgets', 'action' => 'index']);
        }

        $widgets = \Widget::find([
            'conditions' => 'id IN ({ids:array})',
            'bind'       => ['ids' => $ids],
        ]);

        $count = 0;

        foreach ($widgets as $widget) {
            if ($widget->softDelete()) {
                $count++;
            }
        }

        $this->flash->success($count . ' widget(s) deleted');

        return $this->dispatcher->forward(['controller' => 'widgets', 'action' => 'index']);
    }
}
