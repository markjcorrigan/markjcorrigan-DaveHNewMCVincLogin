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

class Users extends \App\Controllers\Authenticated
{
    public function __construct(
        protected readonly Database $database,
        private readonly User $model,
        protected readonly View $view,
        protected Auth $auth
    ) {
    }


//    protected function before(): void
//    {
//        parent::before();
//        error_log("Before method called");
//        $user = $this->auth->getUser();
//        error_log("User: " . print_r($user, true));
//        if (!$user || !$user->is_admin) {
//            error_log("User is not admin");
//            Flash::addMessage('You are not allowed to access that resource.', Flash::WARNING);
//            $response = new Response('', 302, ['Location' => '/']);
//            $response->send();
//            exit;
//        }
//    }




    protected function requireAdmin(): void
    {
        $user = $this->auth->getUser();
        if (!$user || !$user->is_admin) {
            Flash::addMessage('You are not allowed to access that resource.', Flash::WARNING);
            header('Location: /');
            exit;
        }
    }


    public function index(): Response
    {
        $this->requireAdmin();
        $page = $_GET['page'] ?? 1;
        list($users, $currentPage, $totalPages) = $this->model->paginate((int)$page);
        $this->view->renderTemplate('Admin/Users/index.html', [
            'users' => $users,
            'page' => $currentPage,
            'total_pages' => $totalPages,
        ]);
        $output = ob_get_contents(); // Check if output is being generated
        if (empty($output)) {
            echo "No output generated";
            exit;
        }
        return new Response();
    }









//    public function index(): Response
//    {
//        $page = $_GET['page'] ?? 1;
//        list($users, $page, $total_pages) = $this->model->paginate((string) $page);
//
//        $content = $this->view->renderTemplate('Admin/Users/index.html', [
//            'users' => $users,
//            'page' => $page,
//            'total_pages' => $total_pages
//        ]);
//
//        return new Response($content);
//    }

    public function show($id): Response
    {
        $this->requireAdmin();
        $user = $this->getUserOr404($id);
        $content = $this->view->renderTemplate('Admin/Users/show.html', [
            'user' => $user
        ]);
        return new Response($content);
    }


    public function edit($id): Response
    {
        $this->requireAdmin();
        $user = $this->getUserOr404($id);
        $content = $this->view->renderTemplate('Admin/Users/edit.html', [
            'user' => $user
        ]);
        return new Response($content);
    }









    public function delete($id): void
    {
        $this->requireAdmin();
        error_log("Delete method called with ID: $id");
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user = $this->getUserOr404($id);
            $currentUser = $this->auth->getUser();
            if ($currentUser && $currentUser->id != $user->id) {
                error_log("Deleting user with ID: $id");
                User::deleteByID($this->database, $id);
                Flash::addMessage('User deleted', Flash::SUCCESS);
            } else {
                error_log("Cannot delete user with ID: $id");
                Flash::addMessage('You cannot delete yourself or you are not logged in', Flash::WARNING);
            }
        }
        header('Location: /admin/users/index');
        exit;
    }










    public function new(): Response
    {
        $this->requireAdmin();
        $content = $this->view->renderTemplate('Admin/Users/new.html');
        return new Response($content);
    }



    public function create(): Response
    {
        $this->requireAdmin();
        $user = $this->model;
        if ($user->saveAdmin($_POST)) {
            Flash::addMessage('Changes saved', Flash::SUCCESS);
            return $this->redirect('/admin/users/' . $user->id . '/show');
        } else {
            Flash::addMessage('Changes not saved, please try again', Flash::WARNING);
            $content = $this->view->renderTemplate('Admin/Users/new.html', [
                'user' => $user
            ]);
            return new Response($content);
        }
    }


    public function update($id): Response
    {
        $this->requireAdmin();
        $user = $this->getUserOr404($id);
        if ($user->saveAdmin($_POST)) {
            Flash::addMessage('Changes saved', Flash::SUCCESS);
            return $this->redirect('/admin/users/' . $user->id . '/show');
        } else {
            $content = $this->view->renderTemplate('Admin/Users/edit.html', [
                'user' => $user
            ]);
            return new Response($content);
        }
    }





    protected function getUserOr404($id)
    {
        $user = $this->model->findByID($id);

        if (!$user) {
            $content = $this->view->renderTemplate('404.html');
            $response = new Response($content);
            $response->setStatusCode(404);
            $response->send();
            exit;
        }

        return $user;
        }
}
