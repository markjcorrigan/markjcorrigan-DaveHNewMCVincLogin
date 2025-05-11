<?php

namespace App\Models;

use App\Database;

use Framework\Auth;
use Framework\Flash;
use Framework\Model;
use Framework\MVCTemplateViewer;
use Framework\Paginator;
use Framework\Response;
use Framework\Token;
use Framework\Mail;
use PDO;



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

    public function __construct(Database $database, protected MVCTemplateViewer $view)
    {
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

    public function clearErrors(): void
    {
        $this->errors = [];
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

    //The two versions of Validate below still need to be rationalized into the above.
    public function userCreateVal(array $data, ?string $id = null): void
    {

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
        } elseif ($this->emailExists($data["email"], $data["id"] ?? null)) {
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

    public function userUpdateVal(array $data): void
    {
        $this->clearErrors();
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
        } elseif ($this->emailExists($data["email"], $this->id)) {
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
            $this->activation_hash = $hashed_token;


            /////////////NB firstname below is a honeypot field////////////

//            $sql = 'INSERT INTO user (firstname, name, email, password_hash, activation_hash)
//                    VALUES (:firstname, :name, :email, :password_hash, :activation_hash)';
            $sql = 'INSERT INTO user (name, email, password_hash, activation_hash)
                    VALUES (:name, :email, :password_hash, :activation_hash)';
            $conn = $this->database->getConnection();
            $stmt = $conn->prepare($sql);


            ////////////////honeypot field below//////////////
//            $this->firstname = '';
//            $stmt->bindValue(':firstname', $this->firstname, PDO::PARAM_STR);
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
 $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->activation_hash;
      //  $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->activation_hash; //Note that the anc web site code has $this->activation_hash; which I believe is wrong.
        $text = $this->view->render('Signup/activation_email.txt', ['url' => $url]);
        $html = $this->view->render('Signup/activation_email.html', ['url' => $url]);
        Mail::send($this->email, 'Account activation', $text, $html);
    }


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


    public function rememberLogin(): bool
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->remember_token = $token->getValue();

//        $this->expiry_timestamp = time() + 60 * 60 * 24 * 30;  // 30 days from now
        $this->expiry_timestamp = time() + 60 * 60 * 24 * 90; //90 days from now


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


    protected function sendPasswordResetEmail(): Response
    {
        ///  $url = 'https://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token;  /////backed up while hosting locally
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token;

//        $view = new \Framework\View();

        $text = $this->view->render('Password/reset_email.txt', ['url' => $url]);

        $html = $this->view->render('Password/reset_email.html', ['url' => $url]);


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


    public function authenticate(string $email, string $password): mixed
    {
        $user = $this->findByEmail($email);

        //if ($user) {

        if ($user && $user->is_active) {
            if (password_verify($password, $user->password_hash)) {
                return $user;
            }
        }

        return false;
    }



    public function findByID(string $id): ?User
    {
        $sql = 'SELECT * FROM user WHERE id = :id';
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_STR);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($userData) {
            $user = new User($this->database, $this->view);
            $user->setData($userData);
            return $user;
        }
        return null;
    }





    public function findByUserID(string $id): mixed
    {
        $sql = 'SELECT * FROM user WHERE id = :id';
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();
        return $stmt->fetch();
    }


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


    public function activate($token): bool
    {
        $sql = 'UPDATE user SET is_active = 1, is_access = 1, activation_hash = null WHERE activation_hash = :token';
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':token', $token);
        return $stmt->execute();
    }




    public function paginate(int $page): array
    {
        $conn = $this->database->getConnection();

        $total_records = (int) $conn->query('SELECT COUNT(id) FROM user')->fetchColumn();

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


//    public function paginate(int $page): array
//    {
//        $conn = $this->database->getConnection();
//        $total_records = (int)$conn->query('SELECT COUNT(id) FROM user')->fetchColumn();
//        $records_per_page = 5;
//        $paginator = new Paginator($total_records, $records_per_page, $page);
//
//        // Select one page of results
//        $sql = 'SELECT * FROM user ORDER BY name LIMIT :limit OFFSET :offset';
//        $stmt = $conn->prepare($sql);
//        $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
//        $stmt->bindValue(':offset', $paginator->getOffset(), PDO::PARAM_INT);
//        $stmt->execute();
//
//        // Fetch results as User objects
//        $result = [];
//        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//            $user = new User($this->database, $this->view);
//            foreach ($row as $key => $value) {
//                $user->$key = $value;
//            }
//            $result[] = $user;
//        }
//
//        return [$result, $paginator->getPage(), $paginator->getTotalPages()];
//    }



    public function update(string $id, array $data): bool {
        $this->id = $id;
        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->is_access = $data['is_access'] ?? false;
        $this->is_active = $data['is_active'] ?? false;
        $this->is_admin = $data['is_admin'] ?? false;
        $this->memstart = $data['memstart'] ?? false;
        $this->memfin = $data['memfin'] ?? false;

        // Check if firstname is set, if so, it's likely a bot
    if (isset($data['firstname']) && $data['firstname'] != '') {
        // You can log this or take other action if you want
        // For now, just return false
    return false;
    }

    // Only validate and update the password if a value provided
    if (isset($data['password']) && $data['password'] != '') {
        $this->password = $data['password'];

        // Password validation
        if (strlen($this->password) < 6) {
            $this->addError('password', 'Please enter at least 6 characters for the password');
        } elseif (preg_match('/.*[a-z]+.*/i', $this->password) == 0) {
            $this->addError('password', 'Password needs at least one letter');
        } elseif (preg_match('/.*\d+.*/i', $this->password) == 0) {
            $this->addError('password', 'Password needs at least one number');
        } elseif (!isset($data["password_confirmation"]) || $this->password != $data["password_confirmation"]) {
            $this->addError('password', 'Password must match confirmation');
        }
    }

    // Name validation
    if (!preg_match('/^[A-Za-z0-9\x{00C0}-\x{00FF}]+ ?[A-Za-z0-9\x{00C0}-\x{00FF}]+$/u', $this->name)) {
        $this->addError('name', 'Please enter ONE (or max TWO [AlphaNumeric]) user names');
    }

    // Email validation
    if (empty($this->email)) {
        $this->addError("email", "Email is required");
    } elseif (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
        $this->addError('email', 'Invalid email');
    } elseif ($this->emailExists($this->email, $id)) {
        $this->addError('email', 'email already taken');
    }

    if (empty($this->errors)) {
        $sql = 'UPDATE user SET name = :name, email = :email, is_active = :is_active, is_access = :is_access, is_admin = :is_admin, memstart = :memstart, memfin = :memfin';

        // Add password if it's set
        if (isset($this->password)) {
            $sql .= ', password_hash = :password_hash';
        }
        $sql .= "\nWHERE id = :id";

        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_STR);
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

    public function updateProfile(array $data): bool {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->email = $data['email'];
        // Only validate and update the password if a value provided
        if ($data['password'] != '') {
            $this->password = $data['password'];
        }
        $this->validate($data); // Pass the $data array to the validate method
        if (empty($this->errors)) {
            $sql = 'UPDATE user SET name = :name, email = :email';
            // Add password if it's set
            if (isset($this->password)) {
                $sql .= ', password_hash = :password_hash';
            }
            $sql .= "\nWHERE id = :id";
            $conn = $this->database->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
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



//    public function updateProfile(array $data): bool
//    {
//        $this->id = $data['id'];
//        $this->name = $data['name'];
//        $this->email = $data['email'];
//
//
//        // Only validate and update the password if a value provided
//        if ($data['password'] != '') {
//            $this->password = $data['password'];
//        }
//
//        $this->validate();
//
//        if (empty($this->errors)) {
//
//            $sql = 'UPDATE user
//                    SET name = :name, email = :email';
//
//            // Add password if it's set
//            if (isset($this->password)) {
//                $sql .= ', password_hash = :password_hash';
//            }
//
//            $sql .= "\nWHERE id = :id";
//            //$sql .= "WHERE id = :id";
//
//            $conn = $this->database->getConnection();
//            $stmt = $conn->prepare($sql);
//
//            $stmt->bindParam(':name', $this->name, PDO::PARAM_STR);
//            $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
//            //$stmt->bindParam(':id', $this->id, PDO::PARAM_STR);
//            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
//
//
//            // Add password if it's set
//            if (isset($this->password)) {
//                $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
//                $stmt->bindParam(':password_hash', $password_hash, PDO::PARAM_STR);
//            }
//
//            return $stmt->execute();
//        }
//
//        return false;
//    }




    public function saveAdmin($data): bool
    {
        if (isset($this->id)) {
            return $this->updateAdmin($data);
        } else {
            return $this->createAdmin($data);
        }
    }







    public function createAdmin($data): bool
    {
        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->password = $data['password'];
        $this->is_active = ($data['is_active'] ?? '0') == '1';
        $this->is_admin = ($data['is_admin'] ?? '0') == '1';

        $this->validate($data);

        if (empty($this->errors)) {
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO user (name, email, password_hash, is_active, is_admin) VALUES (:name, :email, :password_hash, :is_active, :is_admin)";
            $conn = $this->database->getConnection();
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            $stmt->bindValue(':is_active', $this->is_active, PDO::PARAM_BOOL);
            $stmt->bindValue(':is_admin', $this->is_admin, PDO::PARAM_BOOL);

            try {
                $result = $stmt->execute();
                $this->id = $conn->lastInsertId();
                return $result;
            } catch (\PDOException $e) {
                echo "Error: " . $e->getMessage();
                error_log($e->getMessage());
                return false;
            }
        }

        return false;
    }

    public function updateAdmin($data): bool
    {
        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->is_active = $data['is_active'] ?? false;
        $this->is_admin = $data['is_admin'] ?? false;

        // Only validate and update the password if a value provided
        if ($data['password'] != '') {
            $this->password = $data['password'];
        }

        $this->userUpdateVal($data);

        if (empty($this->errors)) {
            $sql = 'UPDATE user SET name = :name, email = :email, is_active = :is_active, is_admin = :is_admin';
            // Add password if it's set
            if (isset($this->password)) {
                $sql .= ', password_hash = :password_hash';
            }
            $sql .= "\nWHERE id = :id";

            $conn = $this->database->getConnection();
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_STR);
            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':is_active', $this->is_active, PDO::PARAM_BOOL);
            $stmt->bindValue(':is_admin', $this->is_admin, PDO::PARAM_BOOL);

            // Add password if it's set
            if (isset($this->password)) {
                $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
                $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            }

            try {
                return $stmt->execute();
            } catch (\PDOException $e) {
                echo "Error: " . $e->getMessage();
                error_log($e->getMessage());
                return false;
            }
        }

        return false;
    }






    public static function deleteByID(Database $database, $id): void
    {
        $sql = 'DELETE FROM user WHERE id = :id';
        $conn = $database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
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
