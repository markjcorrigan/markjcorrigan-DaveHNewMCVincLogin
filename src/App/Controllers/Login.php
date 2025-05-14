<?php
declare(strict_types=1);

namespace App\Controllers;

use \App\Models\User;
use Framework\Controller;
use Framework\Response;
use Framework\View;
use Framework\Auth;
use Framework\Flash;

class Login extends Controller
{
    public function __construct(protected readonly View $view, private readonly User $model, protected Auth $auth, protected readonly Flash $message)
    {
    }

    public function new(): Response
    {
        $content = $this->view->renderTemplate('Login/new.html');
        return new Response($content);
    }

    public function create(): Response
    {
        if (isset($_POST['email']) && isset($_POST['password'])) {
            $user = $this->model->authenticate($_POST['email'], $_POST['password']);
            $remember_me = isset($_POST['remember_me']);

            if ($user) {
                Auth::login($user, $remember_me);
                Flash::addMessage('Login successful');
                $content = $this->view->renderTemplate('Login/success.html', [
                    'user' => $user,
                ]);
                return new Response($content);
            } else {
                Flash::addMessage('Login unsuccessful, please try again', Flash::WARNING);
                $remember_me = isset($_POST['remember_me']);
                $content = $this->view->renderTemplate('Login/new.html', [
                    'email' => $_POST['email'],
                    'remember_me' => $remember_me
                ]);
                return new Response($content);
            }
        } else {
            Flash::addMessage('Please enter your email and password', Flash::WARNING);
            $content = $this->view->renderTemplate('Login/new.html');
            return new Response($content);
        }
    }

    public function destroy(): Response
    {
        // Unset all of the session variables
        $this->auth->logout();
        $content = $this->view->renderTemplate('/Home/index.html');
        return new Response($content);
    }

    public function showLogoutMessage(): Response
    {
        Flash::addMessage('Logout successful');
        $content = $this->view->renderTemplate('/Home/index.html');
        return new Response($content);
    }
}
