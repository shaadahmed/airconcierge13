<?php

/**
 * Administration sidebar group is not stored in `resources`.
 * Order and visibility mirror legacy admin-partials/sidebar.blade.php.
 *
 * @see docs/adr/020-admin-sidebar-navigation.md
 */
return [
    'administration' => [
        'title' => 'Administration',
        'icon' => 'bx-cog',
        'roles' => ['superadmin', 'admin'],
        'children' => [
            [
                'title' => 'Air BNB Emails',
                'to' => '/admin/airbnbemails',
                'roles' => ['superadmin'],
            ],
            [
                'title' => 'Hostaway Logs',
                'to' => '/admin/hostawaylogs',
                'roles' => ['superadmin'],
            ],
            [
                'title' => 'Email Logs',
                'to' => '/admin/outbound-email-logs',
                'roles' => ['superadmin'],
                'badgeContent' => 'New',
                'badgeClass' => 'bg-warning',
            ],
            [
                'title' => 'Manage Content',
                'to' => '/admin/cms',
                'roles' => ['superadmin', 'admin'],
            ],
            [
                'title' => 'Manage Users',
                'to' => '/admin/users',
                'roles' => ['superadmin'],
            ],
            [
                'title' => 'Manage Roles',
                'to' => '/admin/roles',
                'roles' => ['superadmin'],
            ],
            [
                'title' => 'Management Types',
                'to' => '/admin/management_types',
                'roles' => ['superadmin'],
            ],
            [
                'title' => 'Payment Types',
                'to' => '/admin/payment_types',
                'roles' => ['superadmin'],
                'badgeContent' => 'New',
                'badgeClass' => 'bg-warning',
            ],
            [
                'title' => 'System Resources',
                'to' => '/admin/resources',
                'roles' => ['superadmin'],
            ],
            [
                'title' => 'Platforms',
                'to' => '/admin/platforms',
                'roles' => ['superadmin'],
            ],
            [
                'title' => 'Critical Actions',
                'to' => '/admin/settings/cockpit',
                'roles' => ['superadmin'],
            ],
            [
                'title' => 'Export Guests',
                'to' => '/admin/export/guestemailslist',
                'roles' => ['superadmin'],
            ],
            [
                'title' => 'System Settings',
                'to' => '/admin/settings/system',
                'roles' => ['superadmin'],
                'badgeContent' => 'New',
                'badgeClass' => 'bg-warning',
            ],
            [
                'title' => 'Cron & database rules',
                'to' => '/admin/settings/cron',
                'roles' => ['superadmin', 'admin'],
            ],
            [
                'title' => 'My Profile',
                'to' => '/admin/dashboard/profile',
                'roles' => ['superadmin', 'admin'],
            ],
        ],
    ],

    'icon_map' => [
        'fas fa-tachometer-alt' => 'bx-tachometer',
        'fa fa-users' => 'bx-group',
        'fa fa-book' => 'bx-book',
        'fas fa-user-tie' => 'bx-briefcase',
        'fas fa-chart-bar' => 'bx-bar-chart',
        'fa fa-cogs' => 'bx-cog',
        'fa fa-globe' => 'bx-globe',
    ],
];
