mongolo.document = function ()
{
    'use strict';

    var api = function (query, params, callback)
    {
        params.db = $_GET("db");
        params.collection = $_GET("collection");

        mongolo.api(query, params, callback);
    };

    var get = function (id, callback)
    {
        api("document/get", {id: id}, function (data)
        {
            if (data.error == 0) {
                if (callback !== undefined) {
                    callback(data.result);
                }
            } else {
                if (typeof data.message != "undefined") {
                    mongolo.navigation.showError(data.message);
                    return;
                }
                mongolo.navigation.showError("Error");
            }
        });
    };

    var save = function (document, callback)
    {
        document = mongolo.document.toJson(document);

        api("document/save", {document: document}, function (data) {
            if (data.error == 0) {
                if (callback !== undefined) {
                    callback();
                }
                return;
            }
            mongolo.navigation.showError("Error");
        });
    };

    var remove = function (id, callback)
    {
        api("document/delete", {id: id}, function (data) {
            if (data.error == 0) {
                if (callback !== undefined) {
                    callback();
                }
                return;
            }
            mongolo.navigation.showError("Error");
        });
    };

    var toJson = function (document)
    {
        document = document.replace(/(\r\n|\n|\r)/gm, ""); // remove line breaks

        // convert ObjectID() and other methods to mongo extended json format
        document = mongolo.json.toExtendedJson(document);

        // fix human errors
        return JSON.stringify(Hjson.parse(document));
    };

    return {
        get: get,
        delete: remove,
        toJson: toJson,
        save: save
    };
}();