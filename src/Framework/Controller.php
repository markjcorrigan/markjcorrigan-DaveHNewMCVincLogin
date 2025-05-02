<?php

declare(strict_types=1);

namespace Framework;

abstract class Controller
{
    public function __construct(protected Auth $auth)
    {
    }
    protected Request $request;

    protected Response $response;

    protected TemplateViewerInterface $viewer;
    
    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function setViewer(TemplateViewerInterface $viewer): void
    {
        $this->viewer = $viewer;
    }

    protected function view(string $template, array $data = []): Response
    {
        $this->response->setBody($this->viewer->render($template, $data));

        return $this->response;        
    }

    protected function redirect(string $url): Response
    {
        $this->response->redirect($url);

        return $this->response;
    }

    /*
     * Added from lecture 45 of the Login and Registration course
     */


    /**
     * Require the user to be logged in before giving access to the requested page.
     * Remember the requested page for later, then redirect to the login page.
     *
     * @return void
     */
//    public function requireLogin(): void
//    {
//        if (! Auth::isLoggedIn()) {
//
//            Auth::rememberRequestedPage();
//
//            $this->redirect('/login');
//        }
//    }

    public function requireLogin(): void
    {
        if (! $this->auth->getUser()) {

            //Flash::addMessage('Please login to access that page');
            Flash::addMessage('Please login to access that page', Flash::INFO);

            Auth::rememberRequestedPage();

            $this->redirect('/login');
        }
    }



}