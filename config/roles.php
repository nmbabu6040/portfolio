<?php
// Roles and what each role may do. Edit the lists to change permissions.
return [
    'labels' => ['super_admin' => 'Super admin', 'admin' => 'Admin', 'editor' => 'Editor', 'author' => 'Author', 'subscriber' => 'Subscriber'],
    'rank' => ['super_admin' => 5, 'admin' => 4, 'editor' => 3, 'author' => 2, 'subscriber' => 1],
    'desc' => [
        'super_admin' => 'Everything, including backups and all users.',
        'admin' => 'All content and settings. Can manage Editors, Authors and Subscribers.',
        'editor' => 'All content, all blog posts, media, messages and the newsletter list.',
        'author' => 'Writes and edits only their own blog posts, and uploads media.',
        'subscriber' => 'No access to the admin panel.',
    ],
    'perms' => [
        'dashboard' => ['super_admin', 'admin', 'editor', 'author'],
        'content' => ['super_admin', 'admin', 'editor'],
        'posts.own' => ['super_admin', 'admin', 'editor', 'author'],
        'posts.all' => ['super_admin', 'admin', 'editor'],
        'media' => ['super_admin', 'admin', 'editor', 'author'],
        'media.delete' => ['super_admin', 'admin', 'editor'],
        'messages' => ['super_admin', 'admin', 'editor'],
        'settings' => ['super_admin', 'admin'],
        'users' => ['super_admin', 'admin'],
        'backup' => ['super_admin'],
        'documents' => ['super_admin', 'admin'],
        'activity' => ['super_admin', 'admin'],
    ],
];
