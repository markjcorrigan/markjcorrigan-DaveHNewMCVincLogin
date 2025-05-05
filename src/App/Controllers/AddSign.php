<?php

namespace App\Controllers;

use App\Database;
use App\Models\User;
use App\Models\Usr;
use Exception;
use Framework\Controller;
use Framework\Mail;
use Framework\MVCTemplateViewer;
use Framework\Response;
use Framework\Token;

use Framework\View;
use PDO;

class AddSign extends Controller
{
    public function __construct(protected readonly Database $database, protected Usr $user, protected readonly View $view) {
    }

    public function new(): Response
    {
        $content = $this->view->renderTemplate('AddSign/new.html');
        return new Response($content);
    }



//    public function create(): Response
//    {
//        if (!empty($_POST["firstname"])) {
//            $this->redirect('/signup/success');
//        } else {
//            $user = new Usr($this->database, $this->view);
//            $user->setData($_POST);
//            if ($user->save()) {
//              $_SESSION['activation_hash'] = $user->activation_hash;
//                return $this->redirect('/addsign/success');
//            } else {
//                $content = $this->view->renderTemplate('AddSign/new.html', [
//                    'user' => $user,
//                    'errors' => $user->errors
//                ]);
//                return new Response($content);
//            }
//        }
//    }
    public function create(): Response {
        if (!empty($_POST["firstname"])) {
            $this->redirect('/signup/success');
        } else {
            $user = new Usr($this->database, new MVCTemplateViewer());
            $user->setData($_POST);
            if ($user->save()) {
                $_SESSION['activation_hash'] = $user->activation_hash;
                return $this->redirect('/addsign/success');
            } else {
                $content = $this->view->renderTemplate('AddSign/new.html', [
                    'user' => $user,
                    'errors' => $user->errors
                ]);
                return new Response($content);
            }
        }
    }


//    public function success(): Response {
//        if (isset($_SESSION['activation_hash'])) {
//            $user = $this->findByActivationHash($_SESSION['activation_hash']);
//
//            if ($user instanceof Usr) {
//                $user->sendActivationEmail();
//            }
//            unset($_SESSION['activation_hash']);
//            $content = $this->view->renderTemplate('AddSign/success.html');
//            return new Response($content);
//        }
//    }

    public function success(): Response {
        if (isset($_SESSION['activation_hash'])) {
            $user = $this->findByActivationHash($_SESSION['activation_hash']);

            if ($user instanceof Usr) {
                $user->sendActivationEmail();
            }
            unset($_SESSION['activation_hash']);
            $content = $this->view->renderTemplate('AddSign/success.html');
            return new Response($content);
        }
    }

    public function findByActivationHash($activation_hash): ?Usr {
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare('SELECT * FROM user WHERE activation_hash = :activation_hash');
        $stmt->bindParam(':activation_hash', $activation_hash);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $user = new Usr($this->database, new MVCTemplateViewer());

            foreach ($data as $key => $value) {
                if (property_exists($user, $key)) {
                    $user->$key = $value;
                }
            }
            return $user;
        }
        return null;
    }

    /**
     * Activate a new account
     *
     * @return void
     */
//    public function activate(string $token): void {
//        $this->user->activate($token);
//        Mail::send('markjc@mweb.co.za', 'A new signup', 'A new user just signed up', '<p>A new user just signed up</p>');
//        $this->redirect('/addsign/activated');
//    }

//    public function activate(): Response {
//        $uri = $_SERVER['REQUEST_URI'];
//        $parts = explode('/', $uri);
//        $token = end($parts);
//        $this->user->activate($token);
//        Mail::send('markjc@mweb.co.za', 'A new signup', 'A new user just signed up', '<p>A new user just signed up</p>');
//        $this->redirect('/addsign/activated');
//    }

//    public function activate(): Response {
//        try {
//            $uri = $_SERVER['REQUEST_URI'];
//            $parts = explode('/', $uri);
//            $token = end($parts);
//            if (!empty($token)) {
//                $this->user->activate($token);
//                Mail::send('markjc@mweb.co.za', 'A new signup', 'A new user just signed up', '<p>A new user just signed up</p>');
//                return $this->redirect('/addsign/activated');
//            } else {
//                // Handle the case when token is empty
//                $content = $this->view->renderTemplate('AddSign/error.html', ['error' => 'Token is empty']);
//                return new Response($content);
//            }
//        } catch (Exception $e) {
//            $content = $this->view->renderTemplate('AddSign/error.html', ['error' => $e->getMessage()]);
//            return new Response($content);
//        }
//    }
    public function activate(): Response {
        $uri = $_SERVER['REQUEST_URI'];
        $parts = explode('/', $uri);
        $token = end($parts);
        $this->user->activate($token);
        Mail::send('markjc@mweb.co.za', 'A new signup', 'A new user just signed up', '<p>A new user just signed up</p>');
        return $this->redirect('/addsign/activated');
    }


    /**
     * Show the activation success page
     *
     * @return void
     */
    public function activated(): Response
    {
        $content = $this->view->renderTemplate('AddSign/activated.html');
        return new Response($content);

    }

}