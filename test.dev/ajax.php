<?php

include 'Models/Auth.class.php';
include 'Models/Translator.class.php';
include 'Models/AjaxRequest.class.php';

if (!empty($_COOKIE['sid'])) {
    // check session id in cookies
    session_id($_COOKIE['sid']);
}
session_start();

class AuthorizationAjaxRequest extends AjaxRequest
{
    public $actions = [
        "login" => "login",
        "logout" => "logout",
        "register" => "register",
        "language" => "language",
    ];

    public function checkPOST()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            // Method Not Allowed
            http_response_code(405);
            header("Allow: POST");
            $this->setFieldError("main", "Method Not Allowed", 'index');
            return;
        }
    }

    public function validChr($str)
    {
        return !preg_match('/^[A-Za-z0-9_~\-!@#\$%\^&\*\(\)]+$/',$str);
    }

    public function login()
    {
        $this->checkPOST();
        setcookie("sid", "");

        $username = $this->getRequestParam("username");
        $password = $this->getRequestParam("password");
        $remember = !!$this->getRequestParam("remember-me");

        if (empty($username)) {
            $this->setFieldError("username", "Enter the username", 'index');
            return;
        }

        if (empty($password)) {
            $this->setFieldError("password", "Enter the password", 'index');
            return;
        }

        if ($this->validChr($username)) {
            $this->setFieldError("username", "You cant use such symbols", 'index');
            return;
        }

        if ($this->validChr($password)) {
            $this->setFieldError("password", "You cant use such symbols", 'index');
            return;
        }

        $user = new Auth\User();
        $auth_result = $user->authorize($username, $password, $remember);

        if (!$auth_result) {
            $this->setFieldError("password", "Invalid username or password", 'index');
            return;
        }

        $this->status = "ok";
        $this->setResponse("redirect", "/");
        $this->message = sprintf("Hello, %s! Access granted.", $username);
    }

    public function logout()
    {
        $this->checkPOST();

        setcookie("sid", "");

        $user = new Auth\User();
        $user->logout();

        $this->setResponse("redirect", "/");
        $this->status = "ok";
    }

    public function register()
    {
        $this->checkPOST();

        setcookie("sid", "");

        $username = $this->getRequestParam("username");
        $email = $this->getRequestParam("email");
        $first_name = $this->getRequestParam("firstname");
        $last_name = $this->getRequestParam("lastname");
        $password1 = $this->getRequestParam("password1");
        $password2 = $this->getRequestParam("password2");

        if (empty($username)) {
            $this->setFieldError("username", "Enter the username", 'register');
            return;
        }

        if ($this->validChr($username)) {
            $this->setFieldError("username", "You cant use such symbols", 'register');
            return;
        }

        if (empty($email)) {
            $this->setFieldError("email", "Enter the email", 'register');
            return;
        }

        if ($this->validChr($email)) {
            $this->setFieldError("email", "You cant use such symbols", 'register');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFieldError("email", "Email is not right", 'register');
            return;
        }

        if (empty($first_name)) {
            $this->setFieldError("firstname", "Enter first name", 'register');
            return;
        }

        if ($this->validChr($first_name)) {
            $this->setFieldError("firstname", "You cant use such symbols", 'register');
            return;
        }

        if (empty($last_name)) {
            $this->setFieldError("lastname", "Enter last name", 'register');
            return;
        }

        if ($this->validChr($last_name)) {
            $this->setFieldError("lastname", "You cant use such symbols", 'register');
            return;
        }

        if (empty($password1)) {
            $this->setFieldError("password1", "Enter the password", 'register');
            return;
        }

        if ($this->validChr($password1)) {
            $this->setFieldError("password1", "You cant use such symbols", 'register');
            return;
        }

        if (empty($password2)) {
            $this->setFieldError("password2", "Confirm the password", 'register');
            return;
        }

        if ($password1 !== $password2) {
            $this->setFieldError("password2", "Confirm password does not match", 'register');
            return;
        }

        $file_path = NULL;
        if ($this->checkImage('file')) {
            $file_path = $this->storeImage('file');
        }

        $user = new Auth\User();

        try {
            $new_user_id = $user->create($username, $email, $first_name, $last_name, $password1, $file_path);
        } catch (\Exception $e) {
            $this->setFieldError("username", $e->getMessage(), 'register');
            return;
        }
        $user->authorize($username, $password1);

        $this->message = sprintf("Hello, %s! Thank you for registration.", $username);
        $this->setResponse("redirect", "/");
        $this->status = "ok";
    }

    public function language()
    {
        $this->checkPOST();

        $lang = $this->getRequestParam("set_language");
        $language = new Translator\Translator();

        $language->setLanguage($lang);

        $this->status = "ok";
        $this->setResponse("redirect", "/");

        return;
    }
}

$ajaxRequest = new AuthorizationAjaxRequest($_REQUEST);
$ajaxRequest->redirectResponse();
