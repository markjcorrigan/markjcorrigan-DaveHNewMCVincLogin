<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\User;
use Framework\Controller;
use Framework\Response;
use Framework\View;
use Framework\Auth;

/**
 * Items controller (example)
 *
 * PHP version 7.0
 */
class Items extends Authenticated
{

    public function __construct(protected readonly View $view)
    {

    }



    /**
     * Items index
     *
     * @return void
     */
    public function index(): Response
    {

//        $this->requireLogin();
//        if(! Auth::isLoggedIn()) {
//            exit("access denied");
//        }

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
