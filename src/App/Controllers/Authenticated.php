<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Controller;

abstract class Authenticated extends Controller
{

    protected function before(): void
    {
         $this->requireLogin();
    }
}
