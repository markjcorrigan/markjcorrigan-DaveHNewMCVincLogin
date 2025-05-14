<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Models\User;
use Framework\Controller;
use Framework\Flash;
use Framework\Response;
use Framework\View;
use Framework\Mail;

class Home extends Controller {

    public function __construct(protected readonly View $view, protected readonly Flash $message, protected Mail $mail)
    {
    }

    public function index(): Response {

        $content = $this->view->renderTemplate("Home/index.html", [
            "title" => "Home"
        ]);
        return new Response($content);
    }

}
