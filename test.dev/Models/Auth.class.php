<?php

namespace Auth;

class User
{
    private $id;
    private $username;
    private $db;
    private $user_id;
    private $first_name;
    private $last_name;
    private $email;
    private $file;

    private $db_host = "172.18.0.2";
    private $db_name = "testdb";
    private $db_user = "alex";
    private $db_pass = "passA!";

    private $is_authorized = false;

    public function __construct($username = null, $password = null)
    {
        $this->username = $username;
        $this->connectDb($this->db_name, $this->db_user, $this->db_pass, $this->db_host);
    }

    public function __destruct()
    {
        $this->db = null;
    }

    public static function isAuthorized()
    {
        if (!empty($_SESSION["user_id"])) {
            return (bool) $_SESSION["user_id"];
        }
        return false;
    }

    public function getAccount()
    {
        $query = "select username, first_name, last_name, email, file from users where
            id = :id limit 1";
        $sth = $this->db->prepare($query);

        $sth->execute(
            [
                ":id" => $_SESSION["user_id"]
            ]
        );
        $this->user = $sth->fetch();

        if (!$this->user) {
            return false;
        } else {
            $this->first_name = $this->user['first_name'];
            $this->last_name = $this->user['last_name'];
            $this->email = $this->user['email'];
            $this->file = $this->user['file'];
        }

        $data = [
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
            'file' => $this->file,
        ];

        return $data;
    }

    public function passwordHash($password, $salt = null)
    {
        $salt || $salt = uniqid();
        $hash = password_hash(hash('sha3-512',$password . sha1($salt)), PASSWORD_ARGON2ID);

        return ['hash' => $hash, 'salt' => $salt];
    }

    public function getSalt($username) {
        $query = "select salt from users where username = :username limit 1";
        $sth = $this->db->prepare($query);
        $sth->execute(
            [
                ":username" => $username
            ]
        );
        $row = $sth->fetch();
        if (!$row) {
            return false;
        }
        return $row["salt"];
    }

    public function getHash($username) {
        $query = "select password from users where username = :username limit 1";
        $sth = $this->db->prepare($query);
        $sth->execute(
            [
                ":username" => $username
            ]
        );
        $row = $sth->fetch();
        if (!$row) {
            return false;
        }
        return $row["password"];
    }

    public function authorize($username, $password, $remember=false)
    {
        $query = "select id, username, first_name, last_name, email, file from users where
            username = :username limit 1";
        $sth = $this->db->prepare($query);
        $salt = $this->getSalt($username);

        if (!$salt) {
            return false;
        }

        $hash = $this->getHash($username);
        if (!password_verify(hash('sha3-512',$password . sha1($salt)), $hash)) {
            return false;
        }

        $sth->execute(
            [
                ":username" => $username
            ]
        );
        $this->user = $sth->fetch();
        
        if (!$this->user) {
            $this->is_authorized = false;
        } else {
            $this->is_authorized = true;
            $this->user_id = $this->user['id'];
            $this->first_name = $this->user['first_name'];
            $this->last_name = $this->user['last_name'];
            $this->email = $this->user['email'];
            $this->saveSession($remember);
        }

        return $this->is_authorized;
    }

    public function logout()
    {
        if (!empty($_SESSION["user_id"])) {
            unset($_SESSION["user_id"]);
        }
    }

    public function saveSession($remember = false, $http_only = true, $days = 7)
    {
        $_SESSION["user_id"] = $this->user_id;

        if ($remember) {
            // Save session id in cookies
            $sid = session_id();

            $expire = time() + $days * 24 * 3600;
            $domain = ""; // default domain
            $secure = false;
            $path = "/";

            $cookie = setcookie("sid", $sid, $expire, $path, $domain, $secure, $http_only);
        }
    }

    public function create($username, $email, $first_name, $last_name, $password, $file) {
        $user_exists = $this->getSalt($username);

        if ($user_exists) {
            throw new \Exception("User exists: " . $username, 1);
        }

        $query = "insert into users (username, email, first_name, last_name, file, password, salt)
            values (:username, :email, :first_name, :last_name, :file, :password, :salt)";
        $hashes = $this->passwordHash($password);
        $sth = $this->db->prepare($query);

        try {
            $this->db->beginTransaction();
            $result = $sth->execute(
                [
                    ':username'   => $username,
                    ':email'      => $email,
                    ':first_name' => $first_name,
                    ':last_name'  => $last_name,
                    ':file'       => $file,
                    ':password'   => $hashes['hash'],
                    ':salt'       => $hashes['salt'],
                ]
            );
            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollback();
            echo "Database error: " . $e->getMessage();
            die();
        }

        if (!$result) {
            $info = $sth->errorInfo();
            printf("Database error %d %s", $info[1], $info[2]);
            die();
        } 

        return $result;
    }

    public function connectdb($db_name, $db_user, $db_pass, $db_host)
    {
        try {
            $this->db = new \pdo("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        } catch (\pdoexception $e) {
            echo "database error: " . $e->getmessage();
            die();
        }
        $this->db->query('set names utf8');

        return $this;
    }
}
