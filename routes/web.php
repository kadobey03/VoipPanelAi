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
$router->add('POST', '/calls/sync', 'CallsController@sync');
$router->add('GET', '/calls/record', 'CallsController@record');

// Reports
$router->add('GET', '/reports', 'ReportsController@index');

// Agents
$router->add('GET', '/agents', 'AgentsController@index');

// Numbers
$router->add('GET', '/numbers', 'NumbersController@index');
$router->add('POST', '/numbers/active', 'NumbersController@setActive');
$router->add('POST', '/numbers/spam', 'NumbersController@setSpam');

// Webhook
$router->add('POST', '/webhook/momvoip', 'WebhookController@momvoip');
