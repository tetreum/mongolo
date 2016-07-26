mongolo.collection = function ()
{
    'use strict';
    var currentMilis = new Date().getTime(),
        cmSettings = {
            matchBrackets: true,
            indentUnit: 4,
            indentWithTabs: true,
            mode: {name: "javascript", json: true},
            hintOptions: {
                completeSingle: false,
                list: ['ObjectID("replace_me")', "UTCDateTime(" + currentMilis + ")", "Regex('^foo', 'i')", "Timestamp(0, " + currentMilis + ")", "Javascript('')"]
            },
            extraKeys: {
                "Ctrl-Space": "autocomplete"
            }
        },
        cmDeniedKeys = [37, 38, 39, 40, 13, 8, 32, 188, 190, 16, 56, 57, 50, 219];

    var init = function (autocompleteWords)
    {
        var $form = $("#query-form"),
            $changesQuery = $form.find("#changes-query"),
            $limitSelector = $form.find('[name=limit]');

        cmSettings.hintOptions.list = cmSettings.hintOptions.list.concat(autocompleteWords);

        // load previous query
        if (sessionStorage.lastQuery !== undefined) {
            $form.find("#query-code").val(sessionStorage.lastQuery);
        }
        initQueryEditor();

        // on CTL + S => execute query
        document.addEventListener("keydown", function(e) {
            if (e.keyCode == 83 && (navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey)) {
                e.preventDefault();
                $form.submit();
            }
        }, false);

        $form.on("submit", function (e)
        {
            e.preventDefault();

            var query = $form.find("#query-code").val(),
                action = $form.find('[name=action]').val(),
                page = $form.find('[name=page]').val(),
                limit = $limitSelector.val(),
                sortList = $("[name='sort[]'"),
                sortOrderList = $("[name='order[]'"),
                i = 0,
                sort = {},
                callback = function () {
                    sessionStorage.lastQuery = query;
                    var params = {action: action, query: query, sort: sort, limit: limit, page: page};

                    if (action == "modify") {
                        params.changes_query = $changesQuery.val();
                    }

                    makeQuery(params);
                };

            while (i < sortList.length) {
                var row = $(sortList[i]).val();
                if (row) {
                    sort[row] = $(sortOrderList[i]).val();
                }
                i++;
            }

            if (action == "remove") {
                mongolo.navigation.showConfirm("You are going to remove all documents matching with: \n" + query, callback);
            } else {
                callback();
            }
        }).on("click", "button", function () { // reset page number if user is making a different query
            $form.find('[name=page]').val(1);
        });

        // show/hide some filters depending on what user wants to do
        $form.find('[name="action"]').on("change", function ()
        {
            var action = $(this).val();

            switch (action)
            {
                case "find":
                    $limitSelector.parent().show();
                    $changesQuery.parent().hide();
                    break;
                case "modify":
                    $limitSelector.parent().hide();
                    $changesQuery.parent().show();
                    break;
                case "remove":
                case "insert":
                    $limitSelector.parent().hide();
                    $changesQuery.parent().hide();
                    break;
            }
        });

        $('.collection-menu').on("click", '[data-action="truncate-collection"]', function ()
        {
            mongolo.navigation.showConfirm("¿Do you want to <strong>TRUNCATE</strong> this collection?", function ()
            {
                mongolo.api("collection/truncate", {db: $_GET("db"), collection: $_GET("collection")}, function (data) {
                    if (data.error > 0) {
                        return false;
                    }

                    mongolo.navigation.reload();
                });
            });
        }).on("click", '[data-action="drop-collection"]', function ()
        {
            mongolo.navigation.showConfirm("¿Do you want to <strong>DROP</strong> this collection?", function ()
            {
                var db = $_GET("db");

                mongolo.api("collection/drop", {db: db, collection: $_GET("collection")}, function (data) {
                    if (data.error > 0) {
                        return false;
                    }

                    mongolo.tree.refresh(db);
                    mongolo.navigation.browse("/");
                });
            });
        });

        setResultListeners();
    };

    var initQueryEditor = function ()
    {
        CodeMirror.commands.autocomplete = function(cm) {
            cm.showHint({hint: CodeMirror.hint.customList});
        };

        cmSettings.readOnly = false;

        var editor = CodeMirror.fromTextArea(document.getElementById('query-code'), cmSettings);

        editor.on("keyup", function (cm, event) {
            if (!cm.state.completionActive && cmDeniedKeys.indexOf(event.keyCode) < 0) {
                //CodeMirror.commands.autocomplete(cm, null, {completeSingle: false});
                editor.execCommand("autocomplete");
            }
        });
    };

    var initResultsEditor = function ($selector)
    {
        cmSettings.readOnly = true;
        $selector.find('textarea').each(function ()
        {
            $(this).val(mongolo.json.toSimplifiedJson($(this).val()));

            CodeMirror.fromTextArea(this, cmSettings);
        });
    };

    var setResultListeners = function ($selector)
    {
        if ($selector === undefined) {
            $selector = $(".result");
        }

        $selector.on("click", "[data-action=delete]", function ()
        {
            var id = $(this).parent().data("id");

             if(confirm("¿Do you wanna delete this entry?"))
             {
                 mongolo.document.delete(id, function () {
                     $('#document-' + id).remove();
                 });
             }
        }).on("click", "[data-action=refresh]", function () {
            var id = $(this).parent().data("id");

            mongolo.document.get(id, function (document) {
                $('#document-' + id + " pre").html(document);
            });
        }).on("click", "[data-action=edit]", function () {
            var id = $(this).parent().data("id"),
                $document = $('#document-' + id),
                $saveButton = $document.find('[data-action=save]'),
                $editor = $document.find('.CodeMirror'),
                codeMirror = $editor[0].CodeMirror,
                isReadOnly = codeMirror.getOption("readOnly");

            codeMirror.setOption("readOnly", !isReadOnly);
            $editor.toggleClass("editable");

            if (isReadOnly) {
                codeMirror.focus();
                $saveButton.show();
            } else {
                $saveButton.hide();
            }
        }).on("click", "[data-action=save]", function () {
            var id = $(this).parent().data("id"),
                $document = $('#document-' + id),
                document;

            $document.find('.CodeMirror')[0].CodeMirror.save();
            document = $document.find('textarea').val();

            mongolo.document.save(document, function () {
                // toggle edit mode
                $document.find("[data-action=edit]").click();
            });
        });

        $('.pagination li').on("click", function () {
            var page = $(this).data("page"),
                $form = $("#query-form");

            if (!page) {
                return false;
            }

            $form.find("[name=page]").val(page);
            $form.submit();
        });

        initResultsEditor($selector);
    };

    var makeQuery  = function (params)
    {
        params.db = $_GET("db");
        params.collection = $_GET("collection");
        params.query = mongolo.document.toJson(params.query);

        if (params.changes_query !== undefined) {
            params.changes_query = mongolo.document.toJson(params.changes_query);
        }

        mongolo.navigation.get("/collection/query", params, function (html) {
            switch (params.action) {
                case "insert":
                    var $html = $(html);

                    if ($("#query-results .pagination").length > 0) {
                        $("#query-results .pagination").after($html);
                    } else {
                        $("#query-results").prepend($html);
                    }
                    setResultListeners($html);
                    break;
                case "modify":
                case "remove":
                    $("#query-results").html(html);
                    break;
                case "find":
                default:
                    $("#query-results").html(html);
                    setResultListeners();
                    break;
            }
        });
    };

    var createPageInit = function ()
    {
        $('#new-collection-form').on("submit", function (e) {
            e.preventDefault();

            var name = $("[name=name]").val(),
                db = $_GET("db");

            if (name == "") {
                return;
            }

            mongolo.api("collection/create", {name: name, db: db}, function (data)
            {
                if (data.error > 0 || data.result == false) {
                    mongolo.navigation.showError(data.message);
                    return false;
                }
                mongolo.tree.refresh(db, function () {
                    mongolo.navigation.openCollection(db, name);
                });
            });
        });
    };

    return {
        init: init,
        createPageInit: createPageInit
    };
}();
