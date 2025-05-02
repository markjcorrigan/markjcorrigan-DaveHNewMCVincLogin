<?php

namespace App\Models;

use App\Database;
use Framework\Model;
use Framework\Token;
use PDO;

/**
 * Remembered login model
 *
 * PHP version 7.0
 */
class RememberedLogin extends Model
{
    public string $token_hash;
    public int $user_id;
    public string $expires_at;

    protected Database $database;






    public function __construct(protected User $model)
    {
    }




    /**
     * Find a remembered login model by the token
     *
     * @param string $token The remembered login token
     *
     * @return mixed Remembered login object if found, false otherwise
     */


    public function findByToken(string $token): mixed
    {
        $token = new Token($token);
        $token_hash = $token->getHash();
        $sql = 'SELECT * FROM remembered_logins WHERE token_hash = :token_hash';
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':token_hash', $token_hash, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch();
        if ($data) {
            $this->token_hash = $data['token_hash'];
            $this->user_id = $data['user_id'];
            $this->expires_at = $data['expires_at'];
            return $this;
        } else {
            return false;
        }
    }


//    public static function findByToken(string $token): mixed
//    {
//        $token = new Token($token);
//        $token_hash = $token->getHash();
//
//        $sql = 'SELECT * FROM remembered_logins
//                WHERE token_hash = :token_hash';
//
//        $db = static::getDB();
//        $stmt = $db->prepare($sql);
//        $stmt->bindValue(':token_hash', $token_hash, PDO::PARAM_STR);
//
//        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
//
//        $stmt->execute();
//
//        return $stmt->fetch();
//    }

    /**
     * Get the user model associated with this remembered login
     *
     * @return User The user model
     */
    public function getUser(): User
    {
        return $this->model->findByID($this->user_id);
    }

    /**
     * See if the remember token has expired or not, based on the current system time
     *
     * @return boolean True if the token has expired, false otherwise
     */
    public function hasExpired(): bool
    {
        return strtotime($this->expires_at) < time();
    }

    /**
     * Delete this model
     *
     * @return void
     */
    public function delete(string $id = null): bool
    {
        $sql = 'DELETE FROM remembered_logins WHERE token_hash = :token_hash';
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':token_hash', $this->token_hash, PDO::PARAM_STR);
        return $stmt->execute();
    }


}
