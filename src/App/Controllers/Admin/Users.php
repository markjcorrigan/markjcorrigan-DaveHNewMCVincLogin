<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Database;
use App\Models\User;
use Framework\Request;
use Framework\Response;
use Framework\View;
use Framework\Flash;
use Framework\Auth;
use Framework\Paginator;

/**
 * User admin controller
 *
 * PHP version 7.0
 */
class Users extends \App\Controllers\Authenticated
{
    public function __construct(protected readonly Database $database, private readonly User $model, protected readonly View $view, protected Auth $auth) {
    }

    protected function before(): void
    {
        parent::before();

        if (! $user = $this->auth->getUser()->is_admin) {
            header('HTTP/1.1 403 Forbidden');
            exit('You are not allowed to access that resource.');
        }
    }


    public function index(): Response {
        $page = $_GET['page'] ?? 1;
        list($users, $page, $total_pages) = $this->model->paginate((string) $page);

        $content = $this->view->renderTemplate('Admin/Users/index.html', [
            'users' => $users,
            'page' => $page,
            'total_pages' => $total_pages
        ]);
        return new Response($content);
    }





    public function show(): Response {
        $request = new Request(); // Assuming you have a Request class
        $id = $request->post['id'];
        $user = $this->getUserOr404($id);
        $content = $this->view->renderTemplate('Admin/Users/show.html', [
            'user' => $user
        ]);
        return new Response($content); // You had return new Response($user); which might cause issues if $user is not a string
    }



    public function edit(): Response
    {
        $user = $this->getUserOr404($this->route_params['id']);

        $content = $this->view->renderTemplate('Admin/Users/edit.html', [
            'user' => $user
        ]);
        return new Response($content);
    }

    /**
     * Update the user
     *
     * @return void
     */
    public function update() {
        $user = $this->getUserOr404($this->route_params['id']);
        $userData = $user->getData(); // assuming you have a getData method
        if ($user->update($_POST)) {
            Flash::addMessage('Changes saved', Flash::SUCCESS);
            $this->redirect('/admin/users/' . $user->id . '/show');
        } else {
            $content = $this->view->renderTemplate('Admin/Users/edit.html', [
                'user' => $userData
            ]);
            return new Response($content);
        }
    }


    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user = $this->getUserOr404($this->route_params['id']);
            if ($this->auth->getUser()->id != $user->id) {
                $user->delete();
                Flash::addMessage('User deleted', Flash::SUCCESS);
            }
        }
        $this->redirect('/admin/users/index');
    }


    public function new(): Response
    {
        $content = $this->view->renderTemplate('Admin/Users/new.html');
        return new Response($content);

    }


    public function create(): Response {
        $user = $this->model;
        if ($user->create($_POST)) {
            Flash::addMessage('Changes saved', Flash::SUCCESS);
            $this->redirect('/admin/users/' . $user->id . '/show');
        } else {
            Flash::addMessage('Changes not saved, please try again', Flash::WARNING);
            $content = $this->view->renderTemplate('Admin/Users/new.html', [
                'user' => $user
            ]);
            return new Response($content);
        }
    }



    protected function getUserOr404($id): Response {
        $user = $this->model->findByID($id);
        if ($user) {
            return new Response($this->view->renderTemplate('Admin/Users/show.html', ['user' => $user]));
        } else {
            $content = $this->view->renderTemplate('404.html');
            $response = new Response($content);
            $response->setStatusCode(404);
            return $response;
        }
    }


}
