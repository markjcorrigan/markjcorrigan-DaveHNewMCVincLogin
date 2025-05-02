<?php

use App\Controllers\Password;

$router = new Framework\Router;

$router->add("/admin/{controller}/{action}", ["namespace" => "Admin"]);
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


$router->add('signup/activate/{token:[\da-f]+}', ['controller' => 'Signup', 'action' => 'activate']);
//$router->add('items/index', ['controller' => 'items', 'action' => 'index', "middleware" => "deny"]);
$router->add('items/{action:.*}', ['controller' => 'items', "middleware" => "deny"]);




$router->add("/home/index", ["controller" => "home", "action" => "index"]);
$router->add("/products", ["controller" => "products", "action" => "index"]);
$router->add("/", ["controller" => "home", "action" => "index"]);
$router->add("/{controller}/{action}");

return $router;