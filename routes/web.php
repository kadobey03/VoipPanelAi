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
