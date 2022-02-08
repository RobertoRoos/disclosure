<?php

if (!empty($_POST)) {

    $error = "";
    if (!isset($_POST['secret_cipher']) || !isset($_POST['expiration'])) {
        $error = "Missing cipher or expiration from client";
    } else {
        var_dump($_POST);
    }
}

?>

<!doctype html>
<html class="no-js" lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foundation for Sites</title>
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
        <div class="large-12 cell">
            <div class="callout">
                <h5>Create a new secret share:</h5>

                <div class="grid-x grid-padding-x">
                    <div class="large-12 cell">
                        <label>Secret</label>
                        <textarea rows="3" id="secret"></textarea>
                    </div>
                </div>

                <div class="grid-x grid-padding-x">
                    <div class="large-12 cell">
                        <label>Password</label>
                        <input type="password" id="password"/>
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
                                <input type="text" name="expiration"/>
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
