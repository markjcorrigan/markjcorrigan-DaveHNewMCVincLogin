<?php
namespace App\Models;

use App\Database;
use Framework\Model;
use PDO;

class Search extends Model
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function getResults($term, $page = 1, $records_per_page = 10): array
    {
        $sql = 'SELECT COUNT(*) as total FROM search WHERE title LIKE :term OR description LIKE :term OR keywords LIKE :term';
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $term_count = '%' . $term . '%';
        $stmt->bindParam(':term', $term_count);
        $stmt->execute();
        $total_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_records = $total_result['total'];
        $total_pages = ceil($total_records / $records_per_page);
        $offset = ($page - 1) * $records_per_page;

        $sql = 'SELECT * FROM search WHERE title LIKE :term OR description LIKE :term OR keywords LIKE :term LIMIT :limit OFFSET :offset';
        $stmt = $conn->prepare($sql);
        $term = '%' . $term . '%';
        $stmt->bindParam(':term', $term);
        $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [$result, $page, $total_pages];
    }

    public function getAllResults($page = 1, $records_per_page = 10): array
    {
        $sql = 'SELECT COUNT(*) as total FROM search';
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $total_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_records = $total_result['total'];
        $total_pages = ceil($total_records / $records_per_page);
        $offset = ($page - 1) * $records_per_page;

        $sql = 'SELECT * FROM search LIMIT :limit OFFSET :offset';
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [$result, $page, $total_pages];
    }

    public function getResultById($id)
    {
        $sql = 'SELECT * FROM search WHERE id = :id';
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addResult($title, $description, $url, $keywords): void
    {
        $sql = 'INSERT INTO search (title, description, url, keywords) VALUES (:title, :description, :url, :keywords)';
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':keywords', $keywords);
        $stmt->execute();
    }

    public function updateResult($id, $title, $description, $url, $keywords): void
    {
        $sql = 'UPDATE search SET title = :title, description = :description, url = :url, keywords = :keywords WHERE id = :id';
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':keywords', $keywords);
        $stmt->execute();
    }

    public function deleteResult($id): void
    {
        $sql = 'DELETE FROM search WHERE id = :id';
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        }
}
