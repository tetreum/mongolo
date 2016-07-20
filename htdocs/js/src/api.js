mongolo.api = function (path, params, callback)
{
    'use strict';

    mongolo.navigation.showLoader();
    var method = "post";

    // check if this request doesnt need params
    if (typeof params == "function") {
        callback = params;
        params = {};
        method = "get";
    }

    $[method]("/api/" + path, params, function (data)
    {
        mongolo.navigation.hideLoader();

        if (data == "" || typeof data != "object") {
            mongolo.navigation.showError("Mongolo internal error :(");
            return false;
        }
        else if (data.error > 0)
        {
            mongolo.navigation.parseError(data, callback);
        } else {
            callback(data);
        }
    });
};