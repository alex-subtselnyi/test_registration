<?

if (!empty($_COOKIE['sid'])) {
    // check session id in cookies
    session_id($_COOKIE['sid']);
}
session_start();
require_once 'Models/Auth.class.php';
require_once 'Models/Translator.class.php';

$translator = new Translator\Translator();

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Test</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./vendor/bootstrap/css/bootstrap.min.css">
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
  </head>

  <body>

    <div class="container">

        <?

            include 'views/language.html';

            if (Auth\User::isAuthorized()):
                include 'views/profile.html';
            else :
                include 'views/register.html';
            endif;
        ?>

    </div> <!-- /container -->

    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
    <script src="./vendor/jquery-2.0.3.min.js"></script>
    <script src="./js/ajax-form.js"></script>

  </body>
</html>
