<?php

namespace App\Models;

use App\Database;
use Framework\Model;
use Framework\Token;
use PDO;

class RememberedLogin extends Model
{
    public string $token_hash;
    public int $user_id;
    public string $expires_at;

    protected Database $database;

//    public function __construct(protected User $model)
//    {
//    }
    public function __construct(Database $database, protected User $model)
    {
        $this->database = $database;
    }


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


    public function getUser(): User
    {
        return $this->model->findByID($this->user_id);
    }


    public function hasExpired(): bool
    {
        return strtotime($this->expires_at) < time();
    }


    public function delete(string $id = null): bool
    {
        $sql = 'DELETE FROM remembered_logins WHERE token_hash = :token_hash';
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':token_hash', $this->token_hash, PDO::PARAM_STR);
        return $stmt->execute();
    }
}
