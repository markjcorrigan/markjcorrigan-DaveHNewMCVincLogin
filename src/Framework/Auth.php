<?php

declare(strict_types=1);

namespace Framework;

use App\Database;
use App\Models\RememberedLogin;
use App\Models\User;


readonly class Auth
{
    public function __construct(protected RememberedLogin $rememberedLogin, private User $model)
    {
    }
    /**
     * Login the user
     *
     * @param User $user The user model
     * @param boolean $remember_me Remember the login if true
     *
     * @return void
     */
    public static function login(User $user, bool $remember_me): void
    {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user->id;

        if ($remember_me) {

            if ($user->rememberLogin()) {

                setcookie('remember_me', $user->remember_token, $user->expiry_timestamp, '/');

            }
        }
    }

    /**
     * Logout the user
     *
     * @return void
     */
    public function logout(): void
    {
        // Unset all of the session variables
        $_SESSION = [];

        // Delete the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Finally destroy the session
        session_destroy();

        static::forgetLogin();
    }

    /**
     * Remember the originally-requested page in the session
     *
     * @return void
     */
    public static function rememberRequestedPage(): void
    {
        $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    }

    /**
     * Get the originally-requested page to return to after requiring login, or default to the homepage
     *
     * @return void
     */
    public static function getReturnToPage()
    {
        return $_SESSION['return_to'] ?? '/';
    }

    /**
     * Get the current logged-in user, from the session or the remember-me cookie
     *
     * @return mixed The user model or null if not logged in
     */
    public function getUser(): mixed
    {
        if (isset($_SESSION['user_id'])) {

            return $this->model->findByID($_SESSION['user_id']);

        } else {

            return static::loginFromRememberCookie();
        }
    }

    /**
     * Login the user from a remembered login cookie
     *
     * @return mixed The user model if login cookie found; null otherwise
     */
//    protected static function loginFromRememberCookie(): mixed
//    {
//        $cookie = $_COOKIE['remember_me'] ?? false;
//
//        if ($cookie) {
//
//            $remembered_login = RememberedLogin::findByToken($cookie);
//
//            //if ($remembered_login) {
//            if ($remembered_login && ! $remembered_login->hasExpired()) {
//
//                $user = $remembered_login->getUser();
//
//                static::login($user, false);
//
//                return $user;
//            }
//        }
//    }

    protected function loginFromRememberCookie(): mixed
    {
        $cookie = $_COOKIE['remember_me'] ?? false;
        if ($cookie) {
            $remembered_login = $this->rememberedLogin->findByToken($cookie);
            if ($remembered_login && ! $remembered_login->hasExpired()) {
                $user = $remembered_login->getUser();
                $this->login($user, false);
                return $user;
            }
        }
        return null;
    }




    /**
     * Forget the remembered login, if present
     *
     * @return void
     */
    protected function forgetLogin(): void
    {
        $cookie = $_COOKIE['remember_me'] ?? false;
        if ($cookie) {
            $remembered_login = $this->rememberedLogin->findByToken($cookie);
            if ($remembered_login) {
                $remembered_login->delete();
            }
            setcookie('remember_me', '', time() - 3600); // set to expire in the past
        }
    }

}
