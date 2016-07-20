mongolo.login = function ()
{
    'use strict';

    var init = function ()
    {
        $('#login-container').on("submit", function (e) {
            e.preventDefault();

            var $loginContainer = $('#login-container'),
                username = $loginContainer.find('[name="username"]').val(),
                password = $loginContainer.find('[name="password"]').val();

            if (username == "" || password == "") {
                return false;
            }
            $loginContainer.find(".error").hide();

            // Do not point this to mongolo.navigation.post|mongolo.api as they attempt to fix
            // errors like failed authentication (causing a loop to login)
            $.post("/login", {username: username, password: password}, function (data)
            {
                if (data == "" || data.error > 0) {
                    $loginContainer.find(".error").show();
                    return false;
                }

                console.log("redirect", $_GET("redirect"));
                if ($_GET("redirect") === undefined) {
                    mongolo.navigation.redirect("home");
                } else {
                    mongolo.navigation.redirect(decodeURIComponent($_GET("redirect")));
                }
            });
        });
    };

    return {
        init: init
    };
}();
