<?php

namespace App\Controllers;

use App\Database;
use Framework\Controller;
use Framework\Exceptions\PageNotFoundException;
use Framework\Mail;
use Framework\MVCTemplateViewer;
use Framework\Response;
use Framework\View;
use \App\Models\User;
use PDO;

/**
 * Signup controller
 *
 * PHP version 7.0
 */
class Signup extends Controller
{
    public function __construct(protected readonly Database $database, private readonly User $model, protected readonly View $view) //I need to select either $user or $model.  Went with $model Also View and MVCTemplateViewer
    {
    }

    public function index(): Response
    {
        $users = $this->model->findAll();

        $content = $this->view->renderTemplate("Signup/index.html", [
            "users" => $users,
            "total" => $this->model->getTotal()
        ]);
        return new Response($content);

    }
    public function new(): Response
    {
        $content = $this->view->renderTemplate('Signup/new.html');
        return new Response($content);
    }

    public function create(): Response {
        if (!empty($_POST["firstname"])) {
            $this->redirect('/signup/success');
        } else {
            $user = new User($this->database, new MVCTemplateViewer());
            $user->setData($_POST);
            if ($user->save()) {
                $_SESSION['activation_hash'] = $user->activation_hash;
                return $this->redirect('/signup/success');
            } else {
                $content = $this->view->renderTemplate('Signup/new.html', [
                    'user' => $user,
                    'errors' => $user->errors
                ]);
                return new Response($content);
            }
        }
    }

//    public function create(): Response {  //Previous create method before one above
//        $validationData = [
//            "name" => $this->request->post["name"],
//            "email" => $this->request->post["email"],
//            "password" => $this->request->post["password"],
//            "password_confirmation" => $this->request->post["password_confirmation"],
//        ];
//
//        $this->model->clearErrors();
//
//        $this->model->userCreateVal($validationData);
//
//        if (empty($this->model->getErrors())) {
//            $insertData = [
//                "name" => $validationData["name"],
//                "email" => $validationData["email"],
//                "password_hash" => password_hash($validationData["password"], PASSWORD_DEFAULT),
//
//            ];
//
//            if ($this->model->insert($insertData)) {
//                Flash::addMessage('User created successfully');
//                return $this->redirect("/signup/{$this->model->getInsertID()}/show");
//            }
//        }
//
//        $content = $this->view->renderTemplate('Signup/new.html', [
//            "errors" => $this->model->getErrors(),
//            "user" => [
//                "name" => $validationData["name"],
//                "email" => $validationData["email"],
//                "password" => $validationData["password"],
//                "password_confirmation" => $validationData["password_confirmation"],
//            ]
//        ]);
//
//        return new Response($content);
//    }


    //////////////////////////work on below
    public function update(string $id): Response {
        $user = $this->getUser($id);

        $name = $this->request->post["name"] ?? $user["name"];
        $email = $this->request->post["email"] ?? $user["email"];
        $newPassword = $this->request->post["new_password"] ?? '';
        $confirmNewPassword = $this->request->post["confirm_new_password"] ?? '';



        $validationData = [
            "name" => $name,
            "email" => $email,
            "password" => $newPassword,
            "password_confirmation" => $confirmNewPassword,
            "id" => $id,
        ];


        $this->model->clearErrors();

        $this->model->userUpdateVal($validationData, $id);


        if (empty($this->model->getErrors())) {
            $updateData = [
                "name" => $name,
                "email" => $email,
            ];

            if (!empty($newPassword)) {
                $updateData["password_hash"] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            if ($this->model->update($id, $updateData)) {
                return $this->redirect("/signup/{$id}/show");
            } else {
                $this->model->addError("update", "Failed to update user");
            }
        }

        $content = $this->view->renderTemplate('Signup/edit.html', [
            "errors" => $this->model->getErrors(),
            "user" => [
                "id" => $id,
                "name" => $name,
                "email" => $email,
            ],
        ]);

        return new Response($content);
    }


    public function success(): Response {
        if (isset($_SESSION['activation_hash'])) {
            $user = $this->findByActivationHash($_SESSION['activation_hash']);

            if ($user instanceof User) {
                $user->sendActivationEmail();
            }
            unset($_SESSION['activation_hash']);
            $content = $this->view->renderTemplate('Signup/success.html');
            return new Response($content);
        }
    }

    public function findByActivationHash($activation_hash): ?User {
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare('SELECT * FROM user WHERE activation_hash = :activation_hash');
        $stmt->bindParam(':activation_hash', $activation_hash);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $user = new User($this->database, new MVCTemplateViewer());

            foreach ($data as $key => $value) {
                if (property_exists($user, $key)) {
                    $user->$key = $value;
                }
            }
            return $user;
        }
        return null;
    }


    public function activate(): Response {
        $uri = $_SERVER['REQUEST_URI'];
        $parts = explode('/', $uri);
        $token = end($parts);
        $this->model->activate($token);
        Mail::send('markjc@mweb.co.za', 'A new signup', 'A new user just signed up', '<p>A new user just signed up</p>');
        return $this->redirect('/signup/activated');
    }


    /**
     * Show the activation success page
     *
     * @return void
     */
    public function activated(): Response
    {
        $content = $this->view->renderTemplate('Signup/activated.html');
        return new Response($content);

    }




    public function show(string $id): Response
    {
        $user = $this->getUser($id);

        $content = $this->view->renderTemplate("Signup/show.html", [
            "user" => $user
        ]);
        return new Response($content);

    }


    private function getUser(string $id): array
    {
        $user = $this->model->find($id);

        if ($user === false) {

            throw new PageNotFoundException("User not found");

        }

        return $user;
    }


    public function delete(string $id): Response
    {
        $user = $this->getUser($id);

        $content = $this->view->renderTemplate("Signup/delete.html", [
            "user" => $user
        ]);
        return new Response($content);

    }

    public function destroy(string $id): Response
    {
        $user = $this->getUser($id);

        $this->model->delete($id);

//        return $this->redirect("/users/index");
        return $this->redirect("/signup/index");
    }



}
