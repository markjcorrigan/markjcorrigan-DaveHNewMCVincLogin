<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Auth;
use Framework\Flash;
use Framework\Request;
use Framework\Response;
use Framework\RequestHandlerInterface;
use Framework\MiddlewareInterface;

class IsAdmin implements MiddlewareInterface {
    public function __construct(protected Auth $auth, private readonly Response $response) {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response {
        $user = $this->auth->getUser();
        if (!$user || !$user->is_admin) {
            Flash::addMessage('You need to be an administrator to access this page', Flash::WARNING);
            $this->response->redirect("/");
            return $this->response;
        } else {
            return $next->handle($request);
        }
    }
}
