<?php

namespace App\Controllers;

use Framework\Controller;
use Framework\Exceptions\PageNotFoundException;
use Framework\Flash;
use Framework\Response;
use Framework\View;
use \App\Models\User;

/**
 * Signup controller
 *
 * PHP version 7.0
 */
class Signup extends Controller
{
    public function __construct(protected readonly View $view, private readonly User $model)
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
        $validationData = [
            "name" => $this->request->post["name"],
            "email" => $this->request->post["email"],
            "password" => $this->request->post["password"],
            "password_confirmation" => $this->request->post["password_confirmation"],
        ];

        $this->model->clearErrors();

        $this->model->userCreateVal($validationData);

        if (empty($this->model->getErrors())) {
            $insertData = [
                "name" => $validationData["name"],
                "email" => $validationData["email"],
                "password_hash" => password_hash($validationData["password"], PASSWORD_DEFAULT),

            ];

            if ($this->model->insert($insertData)) {
                Flash::addMessage('User created successfully');
                return $this->redirect("/signup/{$this->model->getInsertID()}/show");
            }
        }

        $content = $this->view->renderTemplate('Signup/new.html', [
            "errors" => $this->model->getErrors(),
            "user" => [
                "name" => $validationData["name"],
                "email" => $validationData["email"],
                "password" => $validationData["password"],
                "password_confirmation" => $validationData["password_confirmation"],
            ]
        ]);

        return new Response($content);
    }


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




















//    public function create(): Response
//    {
//        $password_hash = password_hash($this->request->post["password"], PASSWORD_DEFAULT);
//
//        $data = [
//            "name" => $this->request->post["name"],
//            "email" => $this->request->post["email"],
//            "password" => $this->request->post["password"],
//            "password_confirmation" => $this->request->post["password_confirmation"],
//            'password_hash' =>  $password_hash
//        ];
//
//
//        if ($this->model->insert($data)) {
//
//
//            return $this->redirect("/signup/{$this->model->getInsertID()}/show");
//
//        } else {
//            $content = $this->view->renderTemplate('Signup/new.html', [
//                "errors" => $this->model->getErrors(),
//                "user" => $data
//            ]);
//            return new Response($content);
//
//
//        }
//    }





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






    /**
     * Sign up a new user
     *
     * @return void
     */
//    public function createAction()
//    {
//        $user = new User($_POST);
//
//        if ($user->save()) {
//
//            $user->sendActivationEmail();
//
//            $this->redirect('/signup/success');
//
//        } else {
//
//            View::renderTemplate('Signup/new.html', [
//                'user' => $user
//            ]);
//
//        }
//    }
//
//    /**
//     * Show the signup success page
//     *
//     * @return void
//     */
//    public function successAction()
//    {
//        View::renderTemplate('Signup/success.html');
//    }
//
//    /**
//     * Activate a new account
//     *
//     * @return void
//     */
//    public function activateAction()
//    {
//        User::activate($this->route_params['token']);
//
//        $this->redirect('/signup/activated');
//    }
//
//    /**
//     * Show the activation success page
//     *
//     * @return void
//     */
//    public function activatedAction()
//    {
//        View::renderTemplate('Signup/activated.html');
//    }
}
