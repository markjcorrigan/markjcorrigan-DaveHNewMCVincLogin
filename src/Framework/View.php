<?php

declare(strict_types=1);

namespace Framework;

use App\Models\User;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;



class View
{
    public function __construct(protected Auth $auth)
    {
    }
    /**
     * @throws \Exception
     */
    public static function render(string $view, array $args = []): void
    {
        extract($args, EXTR_SKIP);

        $file = dirname(__DIR__) . "'/../views'/$view";  // relative to Framework directory

        if (is_readable($file)) {
            require $file;
        } else {
            throw new \Exception("$file not found");
        }
    }


    public function renderTemplate(string $template, array $args = []): void
    {
        echo static::getTemplate($template, $args);
    }

    public function getTemplate(string $template, array $args = []): string
    {
        static $twig = null;

        if ($twig === null) {
            $loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/../views');
            $twig = new \Twig\Environment($loader);


            $twig->addGlobal('current_user', $this->auth->getUser());
//            $twig->addGlobal('is_logged_in', Auth::isLoggedIn());

//            $twig->addGlobal('current_user', Auth::getUser());
            $twig->addGlobal('flash_messages', Flash::getMessages());
        }

        try {
            return $twig->render($template, $args);
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            return "Error rendering template: " . $e->getMessage();

        }
    }
//    public static function getTemplate(string $template, array $args = []): string
//    {
//        static $twig = null;
//
//        if ($twig === null) {
//            $loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/../views');
//			$twig = new \Twig\Environment($loader);
//
//
//            $twig->addGlobal('current_user', $this->auth->getUser());
////            $twig->addGlobal('is_logged_in', Auth::isLoggedIn());
//
////            $twig->addGlobal('current_user', Auth::getUser());
//            $twig->addGlobal('flash_messages', Flash::getMessages());
//        }
//
//        try {
//            return $twig->render($template, $args);
//        } catch (LoaderError|RuntimeError|SyntaxError $e) {
//            return "Error rendering template: " . $e->getMessage();
//
//        }
//    }
}
