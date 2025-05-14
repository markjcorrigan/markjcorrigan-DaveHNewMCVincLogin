<?php

use App\Controllers\Password;

$router = new Framework\Router;

$router->add("/admin/{controller}/{action}", ["namespace" => "Admin"]);

//Admin
$router->add('admin/{controller}/{id:\d+}/{action}', ['namespace' => 'Admin']);
$router->add('admin/{controller}/{action}', ['namespace' => 'Admin']);

//  search
$router->add('search/add', ['controller' => 'Search', 'action' => 'add', 'method' => 'POST', "middleware" => "admin"]);
$router->add('search/delete/{id}', ['controller' => 'Search', 'action' => 'delete', 'method' => 'POST', "middleware" => "admin"]);

$router->add('search/edit/{id}', ['controller' => 'Search', 'action' => 'edit', 'method' => 'GET', "middleware" => "admin"]);
$router->add('search/edit/{id}', ['controller' => 'Search', 'action' => 'edit', 'method' => 'POST', "middleware" => "admin"]);

$router->add('search', ['controller' => 'Search', 'action' => 'index', 'method' => 'GET']);

$router->add("/{title}/{id:\d+}/{page:\d+}", ["controller" => "products", "action" => "showPage"]);
$router->add("/product/{slug:[\w-]+}", ["controller" => "products", "action" => "show"]);
// $router->add("/{controller}/{id:\d+}/{action}");

//$router->add("/{controller}/{id:\d+}/show", ["action" => "show", "middleware" => "message|message"]);
$router->add("/{controller}/{id:\d+}/show", ["action" => "show"]);
$router->add("/{controller}/{id:\d+}/edit", ["action" => "edit"]);
$router->add("/{controller}/{id:\d+}/update", ["action" => "update"]);
$router->add("/{controller}/{id:\d+}/delete", ["action" => "delete"]);
$router->add("/{controller}/{id:\d+}/destroy", ["action" => "destroy", "method" => "post"]);

$router->add('login', ['controller' => 'login', 'action' => 'new']);
$router->add('signup', ['controller' => 'signup', 'action' => 'new']);
$router->add('logout', ['controller' => 'login', 'action' => 'destroy']);
$router->add('password/reset/{token:[\da-f]+}', ['controller' => 'Password', 'action' => 'reset']);

$router->add('signup/activate/{token}', ['controller' => 'Signup', 'action' => 'activate']);

//$router->add('signup/activate/{token:[\da-f]+}', ['controller' => 'Signup', 'action' => 'activate']);
$router->add('profile/show', ['controller' => 'profile', 'action' => 'show', "middleware" => "deny"]);
$router->add('profile/edit', ['controller' => 'profile', 'action' => 'edit', "middleware" => "deny"]);
$router->add('items/index', ['controller' => 'items', 'action' => 'index', "middleware" => "deny"]);
$router->add('items/{action:.*}', ['controller' => 'items', "middleware" => "deny"]);

$router->add("/home/index", ["controller" => "home", "action" => "index"]);
$router->add("/products", ["controller" => "products", "action" => "index"]);
$router->add("/", ["controller" => "home", "action" => "index"]);
$router->add("/{controller}/{action}");

return $router;