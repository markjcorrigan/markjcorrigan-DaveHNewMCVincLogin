<?php

namespace App\Controllers;

use App\Database;
use Framework\Controller;
use Framework\Response;
use Framework\View;
use Framework\Flash;

class Search extends Controller
{
    public function __construct(
        protected readonly Database $database,
        protected readonly View     $view
    )
    {
    }

    public function index(): Response
    {
        $page = (int)($_GET['page'] ?? 1);
        $term = $_GET['term'] ?? '';
        $searchModel = new \App\Models\Search($this->database);
        if (!empty($term)) {
            $results = $searchModel->getResults($term, $page);
        } else {
            $results = $searchModel->getAllResults($page);
        }
        error_log(var_export($results[2], true)); // debug statement
        $content = $this->view->renderTemplate('Search/search.html', [
            'results' => $results[0],
            'page' => $results[1],
            'total_pages' => $results[2],
            'term' => $term,
            'hasTerm' => !empty($term),
        ]);
        return new Response($content);
    }

    public function add(): Response
    {
        $searchModel = new \App\Models\Search($this->database);
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $url = $_POST['url'] ?? '';
        $keywords = $_POST['keywords'] ?? '';

        $searchModel->addResult($title, $description, $url, $keywords);
        Flash::addMessage('Result added successfully', Flash::SUCCESS);
        return $this->redirect('/search');
    }

    public function edit($id): Response
    {
        $searchModel = new \App\Models\Search($this->database);
        $result = $searchModel->getResultById($id);

        if (!$result) {
            Flash::addMessage('Result not found', Flash::WARNING);
            return $this->redirect('/search');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $searchModel->updateResult($id, $_POST['title'], $_POST['description'], $_POST['url'], $_POST['keywords'] ?? '');
            Flash::addMessage('Result updated successfully', Flash::SUCCESS);
            return $this->redirect('/search');
        }

        $content = $this->view->renderTemplate('Search/edit.html', ['result' => $result]);
        return new Response($content);
    }

    public function delete($id): Response
    {
        $searchModel = new \App\Models\Search($this->database);
        $searchModel->deleteResult($id);
        Flash::addMessage('Result deleted successfully', Flash::SUCCESS);
        return $this->redirect('/search');
    }
}

