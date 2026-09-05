<?php

return [
    [
        'label'      => 'Widgets',
        'icon'       => 'fas fa-cube',
        'controller' => 'widgets',
        'url'        => 'widgets',
        'roles'      => \Roles::idsByNames(['admin']),
    ],
];
