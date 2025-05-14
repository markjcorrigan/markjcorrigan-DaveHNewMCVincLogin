<?php
declare(strict_types=1);

namespace App\Controllers;

use Framework\Controller;
use Framework\Response;
use Framework\View;
use App\Models\User;

class Password extends Controller
{
    public function __construct(protected User $user, protected readonly View $view)
    {
    }

    public function forgot(): Response
    {
        $content = $this->view->renderTemplate('Password/forgot.html');
        return new Response($content);
    }

    public function requestReset(): Response
    {
        $this->user->sendPasswordReset($_POST['email']);
        $content = $this->view->renderTemplate('Password/reset_requested.html');
        return new Response($content);
    }

    public function reset(string $token): Response
    {
        $result = $this->getUserOrExit($token);
        if ($result instanceof \App\Models\User) {
            $content = $this->view->renderTemplate('Password/reset.html', [
                'token' => $token
            ]);
            return new Response($content);
        } else {
            return $result['error'];
        }
    }

    public function resetPassword(): Response
    {
        $token = $_POST['token'];
        $result = $this->getUserOrExit($token);
        if ($result instanceof \App\Models\User) {
            $user = $result;
            if ($user->resetPassword($_POST['password'])) {
                $content = $this->view->renderTemplate('Password/reset_success.html');
                return new Response($content);
            } else {
                $content = $this->view->renderTemplate('Password/reset.html', [
                    'token' => $token,
                    'user' => $user
                ]);
                return new Response($content);
            }
        } else {
            return $result['error'];
        }
    }

    protected function getUserOrExit(string $token): array|User
    {
        $user = $this->user->findByPasswordReset($token);
        if ($user) {
            return $user;
        } else {
            $content = $this->view->renderTemplate('Password/token_expired.html');
            return ['error' => new Response($content, 401)];
        }
    }
}
