<?php
declare(strict_types=1);

namespace App\Controllers;


use \App\Models\User;
use Framework\Controller;
use Framework\Response;
use Framework\View;
use Framework\Auth;
use Framework\Flash;

/**
 * Login controller
 *
 * PHP version 7.0
 */
class Login extends Controller
{
    public function __construct(protected readonly View $view, private readonly User $model, protected Auth $auth, protected readonly Flash $message)
    {
    }
    /**
     * Show the login page
     *
     * @return void
     */
    public function new(): Response
    {
        $content = $this->view->renderTemplate('Login/new.html');
        return new Response($content);
    }

    /**
     * Log in a user
     *
     * @return void
     */



    public function create(): Response {
      $user = $this->model->authenticate($_POST['email'], $_POST['password']);

        $remember_me = isset($_POST['remember_me']);

        if ($user) {

            Auth::login($user, $remember_me);

            Flash::addMessage('Login successful');
            $content = $this->view->renderTemplate('Login/success.html', [
                'user' => $user,
            ]);
            return new Response($content);
          //  $this->>redirect(Auth::getReturnToPage());  //video 44
        } else {
            Flash::addMessage('Login unsuccessful, please try again', Flash::WARNING);
            $remember_me = isset($_POST['remember_me']);
            $content = $this->view->renderTemplate('Login/new.html', [
                'email' => $_POST['email'],
                'remember_me' => $remember_me
            ]);
            return new Response($content);
        }
    }


    /////NB above is the code that I am working on getting aligned with David's course.  Below works sort of per last git save
//    public function create(): Response {
//     //   $user = $this->model->findByEmail($_POST['email']);  NB works
//        $user = $this->model->authenticate($_POST['email'], $_POST['password']);
//
//        if ($user) {
//            $_SESSION['user_id'] = $user->id;
////
////            $remember_me = isset($_POST['remember_me']);
////            Auth::login($user, $remember_me);
//
//
//            Flash::addMessage('Login successful');
//            $content = $this->view->renderTemplate('Login/success.html', [
//                'user' => $user,
//            ]);
//            return new Response($content);
//        } else {
//            Flash::addMessage('Login unsuccessful, please try again', Flash::WARNING);
//            $remember_me = isset($_POST['remember_me']);
//            $content = $this->view->renderTemplate('Login/new.html', [
//                'email' => $_POST['email'],
//                'remember_me' => $remember_me
//            ]);
//            return new Response($content);
//        }
//    }



    /**
     * Log out a user
     *
     * @return void
     */
//    public function destroyAction()
//    {
//        Auth::logout();
//
//        $this->redirect('/login/show-logout-message');
//    }


    public function destroy(): Response {
        // Unset all of the session variables
        $this->auth->logout();
        $content = $this->view->renderTemplate('/Home/index.html');
        return new Response($content);
    }


    /**
     * Show a "logged out" flash message and redirect to the homepage. Necessary to use the flash messages
     * as they use the session and at the end of the logout method (destroyAction) the session is destroyed
     * so a new action needs to be called in order to use the session.
     *
     * @return void
     */


    public function showLogoutMessage(): Response {
        Flash::addMessage('Logout successful');
        $content = $this->view->renderTemplate('/Home/index.html');
        return new Response($content);
    }




}
