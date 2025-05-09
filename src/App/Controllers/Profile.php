<?php

namespace App\Controllers;

use App\Database;
use App\Models\User;
use Framework\Response;
use Framework\View;
use Framework\Auth;
use Framework\Flash;

/**
 * Profile controller
 *
 * PHP version 7.0
 */
class Profile extends Authenticated
{

    public function __construct(protected readonly Database $database, private readonly User $model, protected readonly View $view, protected Auth $auth) {
    }



//    protected function before(): void
//    {
//        parent::before();
//
//        $user = $this->auth->getUser();
//    }


    public function show(): Response {
        $user = $this->auth->getUser();
        $content = $this->view->renderTemplate('Profile/show.html', [
            "user" => $user
        ]);
        return new Response($content);
    }

    public function edit(): Response {
        $user = $this->auth->getUser();
        $content = $this->view->renderTemplate('Profile/edit.html', [
            "user" => $user
        ]);
        return new Response($content);
    }


    public function update(): Response
    {
        $user = $this->auth->getUser();
        $data = $_POST;
        $data['id'] = $user->id; // set the ID of the current user
        if ($this->model->updateProfile($data)) {
            Flash::addMessage('Changes saved');
            return $this->redirect('/profile/show'); // Return the Response object
        } else {
            $content = $this->view->renderTemplate('Profile/edit.html', [
                "user" => $user
            ]);
            return new Response($content); // Return the Response object
        }
    }




//    public function update() {
//        $user = $this->auth->getUser();
//        if ($user->updateProfile($_POST)) {
//            Flash::addMessage('Changes saved');
//            $this->redirect('/profile/show');
//        } else {
//            $content = $this->view->renderTemplate('Profile/edit.html', [
//                "user" => $user
//            ]);
//            return new Response($content);
//        }
//    }


}

