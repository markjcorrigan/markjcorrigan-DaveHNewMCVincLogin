<?php

namespace App\Models;

use App\Database;
use Framework\Mail;
use Framework\Model;
use Framework\MVCTemplateViewer;
use Framework\Response;
use Framework\Token;
use Framework\View;
use PDO;

class Usr extends Model
{
    public string $id;

    public string $email;

    public string $name;
    public string $firstname;

    public string $password;
    public string $password_hash;
    public ?string $password_reset_hash;
    public string $password_reset_token;

    public ?string $password_reset_expires_at;

    public ?string $activation_hash;

    public int $is_active;

    public int $is_access;

    public int $is_admin;

    public string $memstart;

    public string $memfin;

    public ?string $activation_token;

    public ?string $remember_token;

    public ?int $expiry_timestamp;


//    protected ?string $password_reset_token;


    public array $errors = [];

//    public function __construct(Database $database, protected readonly MVCTemplateViewer $view, $data)
    public function __construct(Database $database, protected readonly MVCTemplateViewer $view) {
        parent::__construct($database);
    }


    public function setData($data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }





    public function validate(array $data, ?string $id = null): void {

//        $this->clearErrors(); // Clear any existing errors

        // Name
//        if (empty($data["name"])) {
//            $this->addError("name", "Name is required");
//        }
        if (!preg_match('/^[A-Za-z0-9\x{00C0}-\x{00FF}]+ ?[A-Za-z0-9\x{00C0}-\x{00FF}]+$/u', $data["name"])) {
            $this->addError('name', 'Please enter ONE (or max TWO [AlphaNumeric]) user names');
        }

        // Email
        if (empty($data["email"])) {
            $this->addError("email", "Email is required");
        } elseif (filter_var($data["email"], FILTER_VALIDATE_EMAIL) === false) {
            $this->addError('email', 'Invalid email');
        }     elseif ($this->emailExists($data["email"], $data["id"] ?? null)) {
            $this->addError('email', 'email already taken');
        }

        // Password

        if (isset($data["password"]) && isset($data["password_confirmation"])) {
            // Password validation...
            if (strlen($data["password"]) < 6) {
                $this->addError('password', 'Please enter at least 6 characters for the password');
            } elseif (preg_match('/.*[a-z]+.*/i', $data["password"]) == 0) {
                $this->addError('password', 'Password needs at least one letter');
            } elseif (preg_match('/.*\d+.*/i', $data["password"]) == 0) {
                $this->addError('password', 'Password needs at least one number');
            } elseif ($data["password"] != $data["password_confirmation"]) {
                $this->addError('password', 'Password must match confirmation');
            }
        }
    }



    public function getTotal(): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM user";

        $conn = $this->database->getConnection();

        $stmt = $conn->query($sql);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$row["total"];
    }

    public function emailExists(string $email, $id = null): bool
    {
        $sql = 'SELECT * FROM user WHERE email = :email';
        if ($id !== null) {
            $sql .= ' AND id != :id';
        }
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        if ($id !== null) {
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch() !== false;
    }


    public function save(): bool
    {
        $data = get_object_vars($this);
        $this->validate($data);

        if (empty($this->errors)) {

            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $token = new Token();
            $hashed_token = $token->getHash();
            $this->activation_token = $token->getValue();

            $this->activation_hash = $hashed_token; // Add this line  NBNBNB Meta AI recommended this.


            /////////////NB firstname below is a honeypot field////////////

            $sql = 'INSERT INTO user (firstname, name, email, password_hash, activation_hash)
                    VALUES (:firstname, :name, :email, :password_hash, :activation_hash)';

            $conn = $this->database->getConnection();
            $stmt = $conn->prepare($sql);


            ////////////////honeypot field below//////////////
            $this->firstname = '';
            $stmt->bindValue(':firstname', $this->firstname, PDO::PARAM_STR);
            ////////////////honeypot field above///////////////
            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            $stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);



            //           try {
            //             return $stmt->execute();
            //         } catch (\PDOException $e) {
            //             error_log($e->getMessage());
            //         }
            //     }

            //     return false;
            // }
            //////////////////            return $stmt->execute();
            //////////////////             }

            ///////////////////  return false;
            /////////////////  }
            ///
            ///
            /// $result = $stmt->execute();
            //var_dump($result);

            try {
                return $stmt->execute();
            } catch (\PDOException $e) {
                error_log($e->getMessage());
            }

        }

        return false;
    }

    public function sendActivationEmail(): void {
        //NB https for server to public
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/addsign/activate/' . $this->activation_token; //Note that the anc web site code has $this->activation_hash; which I believe is wrong.

        $text = $this->view->render('AddSign/activation_email.txt', ['url' => $url]);
        $html = $this->view->render('AddSign/activation_email.html', ['url' => $url]);
        Mail::send($this->email, 'Account activation', $text, $html);
    }






    public function update(string $id, array $data): bool
    {
        $this->firstname = $data['firstname'];
        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->is_access = $data['is_access'] ?? false;
        $this->is_active = $data['is_active'] ?? false;
        $this->is_admin = $data['is_admin'] ?? false;
        $this->memstart = $data['memstart'] ?? false;
        $this->memfin = $data['memfin'] ?? false;

        // Only validate and update the password if a value provided
        if ($data['password'] != '') {
            $this->password = $data['password'];
        }

        $this->validate();

        if (empty($this->errors)) {

            $sql = 'UPDATE user
                    SET firstname = :firstname, name = :name, email = :email, is_active = :is_active, is_access = :is_access, is_admin = :is_admin, memstart = :memstart, memfin = :memfin';

            // Add password if it's set
            if (isset($this->password)) {
                $sql .= ', password_hash = :password_hash';
            }

            $sql .= "\nWHERE id = :id";
            //$sql .= "WHERE id = :id";


            $conn = $this->database->getConnection();
            $stmt = $conn->prepare($sql);


            $stmt->bindValue(':id', $this->id, PDO::PARAM_STR);
            $stmt->bindValue(':firstname', $this->firstname, PDO::PARAM_STR);
            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':is_active', $this->is_active, PDO::PARAM_BOOL);
            $stmt->bindValue(':is_access', $this->is_access, PDO::PARAM_BOOL);
            $stmt->bindValue(':is_admin', $this->is_admin, PDO::PARAM_BOOL);
            $stmt->bindValue(':memstart', $this->memstart, PDO::PARAM_STR);
            $stmt->bindValue(':memfin', $this->memfin, PDO::PARAM_STR);

            // Add password if it's set
            if (isset($this->password)) {
                $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
                $stmt->bindParam(':password_hash', $password_hash, PDO::PARAM_STR);
            }

            return $stmt->execute();
        }

        return false;
    }

    public function activate($value): void {
        $token = new Token($value);
        $hashed_token = $token->getHash();
        $sql = 'UPDATE user SET is_active = 1, is_access = 1, activation_hash = null WHERE activation_hash = :hashed_token';
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':hashed_token', $hashed_token, PDO::PARAM_STR);
        $stmt->execute();
    }


    public function delete(string $id): bool {
        $sql = 'DELETE FROM user WHERE id = :id';
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

}