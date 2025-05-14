<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Auth;
use Framework\Flash;
use Framework\Request;
use Framework\Response;
use Framework\RequestHandlerInterface;
use Framework\MiddlewareInterface;

class RedirectExample implements MiddlewareInterface
{
    public function __construct(protected Auth $auth, private readonly Response $response)
    {
    }
    public function process(Request $request, RequestHandlerInterface $next): Response {
        $authorised = $this->auth->getUser();
        if (!$authorised) {
            Flash::addMessage('PLease log in to access this page', Flash::WARNING);
            $this->response->redirect("/");
            return $this->response;
        } else {
            return $next->handle($request);
        }
    }
}