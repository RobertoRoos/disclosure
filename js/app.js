$(document).foundation()

/**
 * DOM
 */
$(document).ready(function() {

    /**
     * On main form submit - encrypt secret with password and insert cipher into form
     */
    $("form#secret-form").submit(function () {

        var secret = $("textarea#secret").text();
        var password = $("input#password").text();

        var cipher = CryptoJS.AES.encrypt(secret, password);

        $("input#secret_cipher").val(cipher);

        // Continue form POST as normal because the secret and password fields are
        // outside the form.
    });

});