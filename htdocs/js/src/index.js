mongolo.index = function ()
{
    'use strict';

    var init = function ()
    {
        var $addFieldButton = $('[data-action=add-field]');
        $('[data-action=show-create-form]').on("click", function () {
            $('#create-index-form').toggle();
        });
        $addFieldButton.on("click", function (e) {
            e.preventDefault();

            var $template = mongolo.utils.getTemplate("index-field");
            $('#create-index-form ul').append($template);
        });
        $('[data-action=delete]').on("click", function () {
            var name = $(this).data("name");

            mongolo.navigation.showConfirm("¿Do you wanna delete this index? '" + name + "'", function () {
                mongolo.api("index/delete", {db: $_GET("db"), collection: $_GET("collection"), name: name}, function (data) {
                    if (data.error > 0) {
                        return false;
                    }
                    mongolo.navigation.reload();
                });
            });
        });
        $('#create-index-form form').on("submit", function (e)
        {
            e.preventDefault();

            var name = $('[name=name]').val(),
                sortList = $("[name='fields[]'"),
                sortOrderList = $("[name='order[]'"),
                unique = $('[name="unique"]').is(":checked"),
                i = 0,
                fields = {};

            while (i < sortList.length) {
                var row = $(sortList[i]).val();
                if (row) {
                    fields[row] = $(sortOrderList[i]).val();
                }
                i++;
            }

            mongolo.api("index/create", {db: $_GET("db"), collection: $_GET("collection"), name: name, fields: fields, unique: unique}, function (data) {
                if (data.error > 0) {
                    return false;
                }
                mongolo.navigation.reload();
            });
        });

        // add the first field row
        $addFieldButton.click();
    };

    return {
        init: init
    };
}();