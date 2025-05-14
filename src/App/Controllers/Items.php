<?php
declare(strict_types=1);
namespace App\Controllers;

use Framework\Response;
use Framework\View;


class Items extends Authenticated
{

    public function __construct(protected readonly View $view)
    {
    }

    public function index(): Response
    {
        $content = $this->view->renderTemplate('Items/index.html');
        return new Response($content);
    }

    public function new(): Response
    {
        $content = "new action";
        return new Response($content);
    }

    public function show(): Response
    {
        $content = "show action";
        return new Response($content);
    }

}
