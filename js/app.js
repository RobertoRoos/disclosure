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

        var ciphertext = "";

        if (password) {
            var cipher = CryptoJS.AES.encrypt(secret, password);

            ciphertext = cipher.toString();

            console.log(ciphertext);
        }

        $("input#secret_cipher").val(ciphertext);

        // Continue form POST as normal because the secret and password fields are
        // outside the form.
    });

    /**
     * On decrypt form submit - stop regular submit
     */
    $("form#decrypt-form").submit(function (e) {

        e.preventDefault(); // No HTTP request

        var password = $("input#decrypt-password").val();
        var ciphertext = $("input#secret-cipher").val();

        console.log(ciphertext);

        if (password) {

            var decrypted = CryptoJS.AES.decrypt(ciphertext, password)

            var secret;
            try {
                secret = decrypted.toString(CryptoJS.enc.Utf8);
                if (!secret) {
                    secret = "Error";
                }
            } catch (error) {
                secret = "Error";
            }

            $("textarea#secret-result").val(secret);
        }

        return false;
    });

    // Test
    // var secret = "my secret";
    // var password = CryptoJS.enc.Utf8.parse("password");
    // var iv  = CryptoJS.enc.Utf8.parse('1583288699248111');
    //
    // var cipher = CryptoJS.AES.encrypt(secret, password, {iv: iv});
    //
    // console.log(cipher);
    //
    // var ciphertext = cipher.toString();
    //
    // console.log(ciphertext);
    //
    // // var cipher2 = CryptoJS.enc.Base64.parse(ciphertext);
    // var cipher2 = CryptoJS.lib.CipherParams.create({
    //     ciphertext: CryptoJS.enc.Base64.parse(ciphertext )
    // });
    //
    // console.log(cipher2);
    //
    // var decrypted = CryptoJS.AES.decrypt(cipher2, password, {iv: iv});
    //
    // console.log(decrypted);
    //
    // var secret2 = decrypted.toString(CryptoJS.enc.Utf8);
    //
    // console.log(secret2);

});