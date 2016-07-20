mongolo.json = function ()
{
    'use strict';

    var toExtendedJson = function (code)
    {
        // ObjectID("577b57d42d194928bc64ef51") -> { "$oid" : "577b57d42d194928bc64ef51" }
        code = code.replace(/ObjectID\(['|"](.*?)['|"]\)/, '{ "$oid" : "$1"}');

        // Timestamp(1234, 1467815145) -> { "$timestamp" : { "t" : 1467815145, "i" : 1234 } }
        code = code.replace(/Timestamp\(([0-9]*?)\,[\s]*([0-9]*?)\)/, '{ "$timestamp" : { "t" : $2, "i" : $1 } }');

        // UTCDateTime(1467815145) -> { "$date" : 1467815145 }
        code = code.replace(/UTCDateTime\(([0-9]*?)\)/, '{ "$date" : $1}');

        // Regex('^foo', 'i') -> { "$regex" : "^acme.*corp", "$options" : "i" }
        code = code.replace(/Regex\('(.*?)(?='|")['|"]\,[\s]*['|"](.*?)(?='|")['|"]\)/, '{ "$regex" : "$1", "$options" : "$2" }');

        return code;
    };

    var toSimplifiedJson = function (code)
    {
        // { "$oid" : "577b57d42d194928bc64ef51" } -> ObjectID("577b57d42d194928bc64ef51")
        code = code.replace(/\{[\s]+"\$oid": "(.*?)"[\s]+\}/, 'ObjectID("$1")');

        // { "$timestamp" : { "t" : 1467815145, "i" : 1234 } } -> Timestamp(1234, 1467815145)
        code = code.replace(/\{[\s]+"\$timestamp": \{[\s]+"t": ([0-9]*?),[\s]+"i": ([0-9]*?)[\s]+\}[\s]+\}/, 'Timestamp($2, $1)');

        // { "$date" : 1467815145 } -> UTCDateTime(1467815145)
        code = code.replace(/\{[\s]+"\$date": ([0-9]*?)[\s]+\}/, "UTCDateTime($1)");

        // { "$regex" : "^acme.*corp", "$options" : "i" } -> Regex('^foo', 'i')
        code = code.replace(/\{[\s]+"\$regex": "(.*?)(?=")",[\s]+"\$options": "(.*?)(?=")"[\s]+\}/, "Regex('$1', '$2')");

        return code;
    };

    return {
        toExtendedJson: toExtendedJson,
        toSimplifiedJson: toSimplifiedJson
    };
}();