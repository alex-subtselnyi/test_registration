<?php

namespace Translator;

class Translator{

    private $translations;

    public function __construct(){
        $this->translations = [
            'username'  => [
                'en' => 'Username',
                'ru' => 'Имя пользователя'
            ],
            'password' => [
                'en' => 'Password',
                'ru' => 'Пароль'
            ],
            'email' => [
                'en' => 'Email',
                'ru' => 'Email'
            ],
            'remember_me' => [
                'en' => 'Remember me',
                'ru' => 'Запомнить меня'
            ],
            'file' => [
                'en' => 'Choose file',
                'ru' => 'Выбрать файл'
            ],
            'no_account' => [
                'en' => 'Not have an account',
                'ru' => 'Нет еще профиля'
            ],
            'sign_in' => [
                'en' => 'Sign In',
                'ru' => 'Войти'
            ],
            'register' => [
                'en' => 'Register',
                'ru' => 'Зарегестрироваться'
            ],
            'first_name' => [
                'en' => 'First Name',
                'ru' => 'Имя'
            ],
            'last_name' => [
                'en' => 'Last Name',
                'ru' => 'Фамилия'
            ],
            'confirm_password' => [
                'en' => 'Confirm Password',
                'ru' => 'Подтвердить пароль'
            ],
            'have_account' => [
                'en' => 'Already have an account',
                'ru' => 'Уже есть профиль'
            ],
            'profile' => [
                'en' => 'Profile',
                'ru' => 'Профиль'
            ],
            'logout' => [
                'en' => 'Logout',
                'ru' => 'Выйти'
            ],
            'set_language' => [
                'en' => 'Set Language',
                'ru' => 'Установить Язык'
            ]

        ];
    }

    public function translate($word){
        echo $this->translations[$word][$_SESSION["language"]];
    }

    public function setLanguage($lang)
    {
        if (!empty($_SESSION["language"])) {
            unset($_SESSION["language"]);
        }

        $_SESSION["language"] = $lang;
    }

    public function getLanguage()
    {
        return $_SESSION["language"];
    }
}