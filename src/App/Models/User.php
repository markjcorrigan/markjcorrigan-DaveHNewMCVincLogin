<?php

namespace App\Models;

use App\Database;
use DateTime;
use Framework\Auth;
use Framework\Model;
use Framework\MVCTemplateViewer;
use Framework\Paginator;
use Framework\Response;
use PDO;
use Framework\Token;
use Framework\Mail;
use Framework\View;

/**
 * User model
 *
 * PHP version 7.0
 */
class User extends Model
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



//    public function __construct(Database $database, protected View $view) {
//        parent::__construct($database);
//
//    }

    public function __construct(Database $database, protected readonly MVCTemplateViewer $view) {
        parent::__construct($database);
    }





    public function clearErrors(): void
    {
        $this->errors = [];
    }


    public function userCreateVal(array $data, ?string $id = null): void {

        $this->clearErrors(); // Clear any existing errors

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

    public function userUpdateVal(array $data, ?string $id): void {
        $this->clearErrors(); // Clear any existing errors
        // Use the validation logic from validateUser
        // Name
        if (!preg_match('/^[A-Za-z0-9\x{00C0}-\x{00FF}]+ ?[A-Za-z0-9\x{00C0}-\x{00FF}]+$/u', $data["name"])) {
            $this->addError('name', 'Please enter ONE (or max TWO [AlphaNumeric]) user names');
        }
        // Email
        if (empty($data["email"])) {
            $this->addError("email", "Email is required");
        } elseif (filter_var($data["email"], FILTER_VALIDATE_EMAIL) === false) {
            $this->addError('email', 'Invalid email');
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











    public function emailExistsUpdate($email, $id = null): bool //NB the custom validateUpdate check above uses this custome emailEsistsUpdate method to enable me to save same email to database while updating other fields
    {
        $user = static::findByEmail($email);

        if ($user) {
            if ($user->id == $id) {
                return false; // Email belongs to the same user
            } else {
                return true; // Email belongs to a different user
            }
        }

        return false; // Email doesn't exist
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

    /**
     * See if a user record already exists with the specified email
     *
     * @param string $email email address to search for
     *
     * @return boolean  True if a record already exists with the specified email, false otherwise
     */
    public function emailExists(string $email, $id = null): bool {
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


    public function rememberLogin(): bool
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->remember_token = $token->getValue();

        $this->expiry_timestamp = time() + 60 * 60 * 24 * 30;  // 30 days from now

        $sql = 'INSERT INTO remembered_logins (token_hash, user_id, expires_at)
                VALUES (:token_hash, :user_id, :expires_at)';


        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expiry_timestamp), PDO::PARAM_STR);

        return $stmt->execute();
    }


    public function sendPasswordReset(string $email): void
    {
        $user = static::findByEmail($email);

        if ($user) {

            if ($user->startPasswordReset()) {

                $user->sendPasswordResetEmail();

            }
        }
    }

    protected function startPasswordReset(): bool
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->password_reset_token = $token->getValue();

        $expiry_timestamp = time() + 60 * 60 * 2;  // 2 hours from now

        $sql = 'UPDATE user
                SET password_reset_hash = :token_hash,
                    password_reset_expires_at = :expires_at
                WHERE id = :id';

        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiry_timestamp), PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

//    protected function sendPasswordResetEmail()
//    {
//        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token;
//
//        $text = View::getTemplate('Password/reset_email.txt', ['url' => $url]);
//        $html = View::getTemplate('Password/reset_email.html', ['url' => $url]);
//
//        Mail::send($this->email, 'Password reset', $text, $html);
//    }
    protected function sendPasswordResetEmail(): Response
    {
        ///  $url = 'https://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token;  /////backed up while hosting locally
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token;  /////backed up while hosting locally
//        $url = 'https://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token;


//        $view = new \Framework\View();

        $text = $this->view->render('Password/reset_email.txt', ['url' => $url]);

        $html  = $this->view->render('Password/reset_email.html', ['url' => $url]);



        Mail::send($this->email, 'Password reset', $text, $html);
        return new Response(); // or return a specific response object

    }
    public function findByPasswordReset($token)
    {
        $token = new Token($token);
        $hashed_token = $token->getHash();
        $sql = 'SELECT * FROM user WHERE password_reset_hash = :token_hash';
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($userData) {
            $user = new User($this->database, $this->view);
            foreach ($userData as $key => $value) {
                $user->$key = $value;
            }
            // Check password reset token hasn't expired
            if (strtotime($user->password_reset_expires_at) > time()) {
                return $user;
            }
        }
    }


//    public  function findByPasswordReset($token)
//    {
//        $token = new Token($token);
//        $hashed_token = $token->getHash();
//
//        // $sql = 'SELECT * FROM users
//        //         WHERE password_reset_token = :token';
//
//        $sql = 'SELECT * FROM user
//                WHERE password_reset_hash = :token_hash';
//
//
//        $conn = $this->database->getConnection();
//        $stmt = $conn->prepare($sql);
//        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
//
//        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
//
//        $stmt->execute();
//
//        $user = $stmt->fetch();
//
//        if ($user) {
//
//            // Check password reset token hasn't expired
//            if (strtotime($user->password_reset_expires_at) > time()) {
//
//                return $user;
//            }
//        }
//
//    }

//    public function findByPasswordReset($token)
//    {
//        $token = new Token($token);
//        $hashed_token = $token->getHash();
//
//        // $sql = 'SELECT * FROM users
//        //         WHERE password_reset_token = :token';
//
//        $sql = 'SELECT * FROM user
//                WHERE password_reset_token = :token';
//
//        $conn = $this->database->getConnection();
//        $stmt = $conn->prepare($sql);
//
//        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
//
//        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
//
//        $stmt->execute();
//
//        $user = $stmt->fetch();
//
//        if ($user) {
//
//            // Check password reset token hasn't expired
//            if (strtotime($user->password_reset_expires_at) > time()) {
//
//                return $user;
//            }
//        }
//
//    }


    public function findByEmail($email, $id = null): ?User
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
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $userData = $stmt->fetch();
        if ($userData) {
            $user = new User($this->database, $this->view);

            foreach ($userData as $key => $value) {
                $user->$key = $value;
            }
            return $user;
        }
        return null; // You might want to return null if no user is found
    }


    public function authenticate(string $email, string $password): mixed
    {
        $user = $this->findByEmail($email);

        //if ($user) {
//        if ($user && $user->is_active) {
        if ($user) {
            if (password_verify($password, $user->password_hash)) {
                return $user;
            }
        }

        return false;
    }




    public function findByID(string $id): ?User {
        $sql = 'SELECT * FROM user WHERE id = :id';
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($userData) {
            foreach ($userData as $key => $value) {
                $this->$key = $value;
            }
            return $this;
        }
        return null;
    }




    public function findByUserID(string $id): mixed {
        $sql = 'SELECT * FROM user WHERE id = :id';
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();
        return $stmt->fetch();
    }









    public function sendActivationEmail(): Response
    {


    $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->activation_hash; /////backed up while hosting locally
//        $url = 'https://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->activation_token;


        $text = $this->view->render('Signup/activation_email.txt', ['url' => $url]);
        $html  = $this->view->render('Signup/activation_email.html', ['url' => $url]);

        Mail::send($this->email, 'Account activation', $text, $html);

        return new Response(); // or return a specific response object
    }





    //public function resetPassword($data)

    public function resetPassword($password): bool
    {
        $this->password = $password;
        $data = ['password' => $password];
        $this->validatePassword($data);
        if (empty($this->errors)) {
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
            $sql = 'UPDATE user SET password_hash = :password_hash, password_reset_hash = NULL, password_reset_expires_at = NULL WHERE id = :id';
            $conn = $this->database->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            return $stmt->execute();
        }
        return false;
    }





    protected function validatePassword(array $data): void
    {
        if (strlen($data['password']) < 6) {
            $this->addError('password', 'Please enter at least 6 characters for the password');
        } elseif (preg_match('/.*[a-z]+.*/i', $data['password']) == 0) {
            $this->addError('password', 'Password needs at least one letter');
        } elseif (preg_match('/.*\d+.*/i', $data['password']) == 0) {
            $this->addError('password', 'Password needs at least one number');
        }
    }


    /**
     * Clear the password reset token and expiry from the model
     *
     * @return void
     */
    public function clearPasswordReset(): void
    {
        $sql = 'UPDATE user
                SET password_reset_token = NULL, password_reset_expires_at = NULL
                WHERE id = :id';



        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':id', $this->id, PDO::PARAM_STR);

        $stmt->execute();
    }


    public function activate(string $value): void
    {
        $token = new Token($value);
        $hashed_token = $token->getHash();

        // $sql = 'UPDATE users
        //         SET is_active = 1,
        //   ///////////////////////////////////////////////////////////////      is_access=1
        //         activation_token = null
        //         WHERE activation_token = :hashed_token';

        $sql = 'UPDATE user
                SET is_active = 1,
                is_access = 1,
                    activation_hash = null
                WHERE activation_hash = :hashed_token';


        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':hashed_token', $hashed_token, PDO::PARAM_STR);

        $stmt->execute();

        // Error logging per dave.  Not sure it works?     if ($stmt->execute() === false) {

        // print_r($stmt->errorInfo());

    }


    /**
     * Update the user's profile
     *
     * @param array $data Data from the edit profile form
     *
     * @return boolean  True if the data was updated, false otherwise
     */
    public function updateProfile(array $data): bool
    {
        $this->name = $data['name'];
        $this->email = $data['email'];

        // Only validate and update the password if a value provided
        if ($data['password'] != '') {
            $this->password = $data['password'];
        }

        $this->validate();

        if (empty($this->errors)) {

            $sql = 'UPDATE user
                    SET name = :name, email = :email';

            // Add password if it's set
            if (isset($this->password)) {
                $sql .= ', password_hash = :password_hash';
            }

            $sql .= "\nWHERE id = :id";
            //$sql .= "WHERE id = :id";



            $conn = $this->database->getConnection();
            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
            //$stmt->bindParam(':id', $this->id, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);


            // Add password if it's set
            if (isset($this->password)) {
                $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
                $stmt->bindParam(':password_hash', $password_hash, PDO::PARAM_STR);
            }

            return $stmt->execute();
        }

        return false;
    }



    public function paginate(string $page): array
    {
        $conn = $this->database->getConnection();

        $total_records = (int) $conn->query('SELECT COUNT(id) FROM users')->fetchColumn();

        $records_per_page = 5;

        $paginator = new Paginator($total_records, $records_per_page, $page);

        // Select one page of results
        $sql = 'SELECT * FROM user
                ORDER BY name
                LIMIT :limit
                OFFSET :offset';

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $paginator->getOffset(), PDO::PARAM_INT);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [$result, $paginator->getPage(), $paginator->getTotalPages()];
    }

    /**
     * Update the user record
     *
     *
     * @param string $id
     * @param array $data
     * @return boolean  True if the data was updated, false otherwise
     */
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

    /**
     * Delete this user record
     *
     * @param string $id
     * @return void
     */
    public function delete(string $id): bool
    {
        $sql = 'DELETE FROM user
                WHERE id = :id';

        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':id', $this->id, PDO::PARAM_STR);

        $stmt->execute();


    }

}
