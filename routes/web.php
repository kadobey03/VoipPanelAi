<?php
// Auth
$router->add('GET', '/login', 'AuthController@login');
$router->add('POST', '/login', 'AuthController@login');
$router->add('GET', '/logout', 'AuthController@logout');

// Dashboard
$router->add('GET', '/', 'DashboardController@index');

// Users
$router->add('GET', '/users', 'UserController@index');
$router->add('GET', '/users/create', 'UserController@create');
$router->add('POST', '/users/create', 'UserController@create');
$router->add('GET', '/users/edit', 'UserController@edit'); // ?id=
$router->add('POST', '/users/edit', 'UserController@edit'); // ?id=
$router->add('POST', '/users/delete', 'UserController@delete');

// Balance
$router->add('GET', '/balance', 'BalanceController@index');
$router->add('POST', '/balance', 'BalanceController@index');

// Groups
$router->add('GET', '/groups', 'GroupController@index');
$router->add('GET', '/groups/edit', 'GroupController@edit');
$router->add('POST', '/groups/edit', 'GroupController@edit');
$router->add('GET', '/groups/create', 'GroupController@create');
$router->add('POST', '/groups/create', 'GroupController@create');
$router->add('GET', '/groups/topup', 'GroupController@topup');
$router->add('POST', '/groups/topup', 'GroupController@topup');
$router->add('GET', '/groups/show', 'GroupController@show');

// Calls
$router->add('GET', '/calls', 'CallsController@index');
$router->add('GET', '/calls/history', 'CallsController@history');
$router->add('POST', '/calls/sync', 'CallsController@sync');
$router->add('GET', '/calls/sync-history', 'CallsController@sync');
$router->add('POST', '/calls/sync-history', 'CallsController@sync');
$router->add('POST', '/calls/sync-historical-call-stats', 'CallsController@syncHistoricalCallStats');
$router->add('GET', '/calls/sync-call-stats', 'CallsController@syncCallStats');
$router->add('GET', '/calls/sync-historical-call-stats', 'CallsController@syncHistoricalCallStats');
$router->add('POST', '/calls/sync-call-stats', 'CallsController@syncCallStats');
$router->add('GET', '/calls/record', 'CallsController@record');

// Cron endpoints (token required)
$router->add('GET', '/cron/calls/sync', 'CallsController@syncCron');

// Reports
$router->add('GET', '/reports', 'ReportsController@index');

// Agents
$router->add('GET', '/agents', 'AgentsController@index');
$router->add('POST', '/agents/toggle-hidden', 'AgentsController@toggleHidden');
$router->add('POST', '/agents/sync', 'AgentsController@syncAgents');
$router->add('POST', '/agents/toggle-active', 'AgentsController@toggleActive');

// Numbers
$router->add('GET', '/numbers', 'NumbersController@index');
$router->add('POST', '/numbers/active', 'NumbersController@setActive');
$router->add('POST', '/numbers/spam', 'NumbersController@setSpam');

// Webhook
$router->add('POST', '/webhook/momvoip', 'WebhookController@momvoip');

// Topup requests
$router->add('GET', '/topups', 'TopupController@index');
$router->add('POST', '/topups/approve', 'TopupController@approve');
$router->add('POST', '/topups/reject', 'TopupController@reject');
$router->add('GET', '/topups/receipt', 'TopupController@receipt');

// Profile
$router->add('GET', '/profile', 'ProfileController@index');
$router->add('POST', '/profile', 'ProfileController@index');

// Admin impersonation
$router->add('GET', '/admin/impersonate', 'AdminController@impersonate'); // ?id=
$router->add('GET', '/admin/impersonate/stop', 'AdminController@stopImpersonate');

// Payment methods (super admin)
$router->add('GET', '/payment-methods', 'PaymentMethodsController@index');
$router->add('GET', '/payment-methods/create', 'PaymentMethodsController@create');
$router->add('POST', '/payment-methods/create', 'PaymentMethodsController@create');
$router->add('GET', '/payment-methods/edit', 'PaymentMethodsController@edit');
$router->add('POST', '/payment-methods/edit', 'PaymentMethodsController@edit');
$router->add('POST', '/payment-methods/delete', 'PaymentMethodsController@delete');

// Transactions (balance history)
$router->add('GET', '/transactions', 'TransactionsController@index');

// Settings
$router->add('GET', '/settings', 'SettingsController@index');
$router->add('POST', '/settings', 'SettingsController@index');

// Change language
$router->add('POST', '/change-lang', function() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $lang = $_POST['lang'] ?? 'tr';

    // Validate language
    if (in_array($lang, ['tr', 'en'])) {
        try {
            \App\Helpers\Lang::set($lang);
        } catch (Exception $e) {
            // Log error if needed
            error_log('Language change error: ' . $e->getMessage());
        }
    }

    // Redirect back to current page or home
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
    header('Location: ' . $referer);
    exit;
});

// Balance helper (topup select)
$router->add('GET', '/balance/topup', 'BalanceMenuController@topupSelect');
