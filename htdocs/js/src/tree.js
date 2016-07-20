mongolo.tree = function ()
{
    'use strict';

    var $tree = $('#navigation-tree ul');

    var init = function ()
    {
        // path is like /collection/entries?db=DB&collection=COL
        if ($_GET("db") !== undefined)
        {
            var collection = $_GET("collection"),
                db = $_GET("db");

            showDatabases(function ()
            {
                toggleDB(db);

                showCollections(db, function () {
                    if (collection !== undefined) {
                        setSelectedCollection(db, collection);
                    }
                });
            });
        }  else {
            showDatabases(function (dbs) {
                // if only has 1 db, show his collections to save visitor clicks
                if (dbs.length == 1)
                {
                    var db = dbs[0];

                    toggleDB(db);
                    showCollections(db);
                }
            });
        }
    };

    var showDatabases = function (callback)
    {
        mongolo.api("db/list", function (data)
        {
            var k;

            for (k in data.result)
            {
                if (!data.result.hasOwnProperty(k)) {
                    continue;
                }
                appendDB(data.result[k]);
            }

            if (callback !== undefined) {
                callback(data.result);
            }
        });
    };

    var showCollections = function (db, callback)
    {
        mongolo.api("db/collections", {db: db}, function (data)
        {
            var k;

            // remove current listed collections
            getDB(db).find('[data-type="collection"]').remove();

            for (k in data.result)
            {
                if (!data.result.hasOwnProperty(k)) {
                    continue;
                }
                appendCollection(db, data.result[k]);
            }

            if (callback !== undefined) {
                callback();
            }
        });
    };

    var appendDB = function (name)
    {
        // check if already exists
        if ($tree.find("[data-type=db][data-name='" + name + "']").length > 0) {
            return;
        }
        var $template = mongolo.utils.getTemplate("tree-db"),
            $a = $template.find('[data-action="new-collection"] a');

        $template[0].dataset.name = name;

        // setup new collection link
        $a.attr("href", $a.attr("href") + "?db=" + name);

        // show collections when clicking db name
        $template.find(".tree-name").text(name).on("click", function ()
        {
            var $parent = $(this).parent(),
                callback = function (){
                    toggleDB(name);
                };

            if ($parent.find("ul li").length < 2) {
                showCollections($parent.data("name"), callback);
            } else {
                callback();
            }
        });

        $tree.append($template);
    };

    var appendCollection = function (db, name)
    {
        var $db = $tree.find("li[data-type=db][data-name='" + db + "'] ul");

        // check if already exists
        if (getCollection(db, name)) {
            return;
        }

        var $template = mongolo.utils.getTemplate("tree-collection"),
            $a = $template.find("a");

        $template[0].dataset.name = name;
        $template.find(".tree-name").text(name);

        $a.attr("href", $a.attr("href") + "?db=" + db + "&collection=" + name).on("click", function () {
            setSelectedCollection(db, name);
        });

        $db.append($template);
    };

    var getCollection = function (db, name)
    {
        var $collection = $tree.find("li[data-type=db][data-name='" + db + "'] ul").find("li[data-name='" + name + "']");

        // check if already exists
        if ($collection.length == 0) {
            return false;
        }
        return $collection;
    };

    var getDB = function (db)
    {
        var $db = $tree.find("li[data-type=db][data-name='" + db + "']");

        // check if already exists
        if ($db.length == 0) {
            return false;
        }
        return $db;
    };

    var setSelectedCollection = function (db, name)
    {
        $tree.find("[data-type=collection] a").removeClass("selected");

        try {
            getCollection(db, name).find("a").addClass("selected")
        } catch (e) {
            console.warn("mongolo.tree -> " + db + "." + name + "() collection not found");
        }

    };

    var toggleDB = function (db)
    {
        var $db = getDB(db);

        $db.find("ul").toggle();
        $db.find(".tree-expander").toggleClass("tree-opened");
    };

    var refresh = function (db, callback)
    {
        // refresh all
        if (db === undefined) {

        } else {
            showCollections(db, callback);
        }
    };

    return {
        init: init,
        getCollection: getCollection,
        refresh: refresh
    };
}();