<?php

return [
    [
        'label'      => 'Tags',
        'icon'       => 'fas fa-tags',
        'controller' => 'tags',
        'url'        => 'tags',
        'roles'      => \Roles::idsByNames(['admin']),
    ],
];
