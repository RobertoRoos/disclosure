<?php

require_once __DIR__ . "/../vendor/autoload.php";

use App\Controller\IndexController;

$controller = new IndexController($_SERVER, $_POST);

$controller->load();

$errors = $controller->getVar("errors");
$url = $controller->getVar("url");
$secret_cipher = $controller->getVar("secret_cipher");
$password_hint = $controller->getVar("password_hint");

?>

<!DOCTYPE html>
<html class="no-js" lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disclosure</title>
    <link rel="stylesheet" href="css/foundation.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="grid-container">
    <div class="grid-x grid-padding-x">
        <div class="large-12 cell">
            <h1>Disclosure</h1>
        </div>
    </div>

    <div class="grid-x grid-padding-x">

        <?php if ($secret_cipher): ?>
            <div class="large-12 cell">
                <div class="callout small secondary">

                    <h5>Enter encryption password to decode the URL:</h5>

                    <form id="decrypt-form" method="get">

                        <?php if ($password_hint) : ?>
                        <p>
                            Hint: <i><?= $password_hint ?></i>
                        </p>
                        <?php endif; ?>

                        <div class="grid-x grid-padding-x">
                            <div class="large-12 cell">
                                <label>Password</label>
                                <input type="password" id="decrypt-password" required/>
                            </div>
                        </div>

                        <input type="hidden" id="secret-cipher" value="<?= $secret_cipher ?>"/>

                        <button type="submit" class="button primary">Decrypt URL</button>

                        <textarea rows="3" id="secret-result" readonly></textarea>

                    </form>

                </div>
            </div>
        <?php endif; ?>

        <?php if ($url): ?>
        <div class="large-12 cell">
            <div class="callout small success">
                <p>
                    Your secret URL, with limited expiration:<br>
                    <a href="<?= $url ?>"><?= $url ?></a>
                </p>
            </div>
        </div>
        <?php endif; ?>

        <div class="large-12 cell">
            <div class="callout">

                <?php foreach ($errors as $error): ?>
                    <div class="callout small alert">
                        <p>Error: <?= $error ?></p>
                    </div>
                <?php endforeach; ?>

                <h5>Create a new secret share:</h5>

                <div class="grid-x grid-padding-x">
                    <div class="large-12 cell">
                        <label>Secret</label>
                        <textarea rows="3" id="secret" required></textarea>
                    </div>
                </div>

                <div class="grid-x grid-padding-x">
                    <div class="large-12 cell">
                        <label>Password</label>
                        <input type="password" id="password" required/>
                    </div>
                </div>

                <form action="index.php" method="post" id="secret-form">

                    <div class="grid-x grid-padding-x">
                        <div class="large-12 cell">
                            <label>Password hint</label>
                            <input type="text" name="hint"/>
                        </div>
                    </div>

                    <div class="grid-x grid-padding-x">
                        <div class="large-12 cell">
                                <label>Expiration</label>
                                <select name="expiration">
                                    <?php
                                    foreach ($controller->getVar('tokens') as $token) {
                                        $ident = $token['identifier'];
                                        $datetime = $token['expiration'];
                                        echo "<option value=$ident>$datetime</option>\n";
                                    }
                                    ?>
                                </select>
                        </div>
                    </div>

                    <input type="hidden" name="secret_cipher" id="secret_cipher"/>

                    <button type="submit" class="button primary">Generate Link</button>

                </form>
            </div>
        </div>
    </div>
</div>


<script src="js/vendor/jquery.js"></script>
<script src="js/vendor/what-input.js"></script>
<script src="js/vendor/foundation.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js" integrity="sha512-E8QSvWZ0eCLGk4km3hxSsNmGWbLtSCSUcewDQPQWZF6pEU8GlT8a5fF32wOl1i8ftdMhssTrF/OhyGWwonTcXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="js/app.js"></script>

</body>
</html>
