$(document).foundation()

/**
 * DOM
 */
$(document).ready(function() {

    /**
     * On main form submit - encrypt secret with password and insert cipher into form
     */
    $("form#secret-form").submit(function () {

        var secret = $("textarea#secret").val();
        var password = $("input#password").val();

        var cipher = "";

        if (password) {
            cipher = CryptoJS.AES.encrypt(secret, password).toString();
        }

        $("input#secret_cipher").val(cipher);

        // Continue form POST as normal because the secret and password fields are
        // outside the form.
    });

    /**
     * On decrypt form submit - stop regular submit
     */
    $("form#decrypt-form").submit(function (e) {

        e.preventDefault(); // No HTTP request

        var password = $("input#decrypt-password").val();
        var cipher = $("input#secret-cipher").val();

        var secret = "";
        if (password) {
            secret = CryptoJS.AES.decrypt(atob(cipher), password)

            $("textarea#secret-result").val(
                secret.toString()
            );
        }

        return false;
    });

});