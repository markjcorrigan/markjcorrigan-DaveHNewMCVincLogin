<?php

namespace App\Controllers;

use \App\Models\User;
use Framework\Controller;

class Account extends Controller
{

    public function validateEmailAction(): void
    {
        $is_valid = ! User::emailExists($_GET['email'], $_GET['ignore_id'] ?? null);  //I still need to work on this
        
        header('Content-Type: application/json');
        echo json_encode($is_valid);
    }
}
