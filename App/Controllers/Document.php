<?php

namespace App\Controllers;

use App\System\AppException;
use App\System\Controller;
use \MongoDB\BSON\ObjectID;
use \MongoDB\Model\BSONDocument;

class Document extends Controller
{
    /**
     * Get document
     * @param string $id
     * @return string
     * @throws AppException
     */
    public function get ($id = null)
    {
        if (empty($id)) {
            $id = (string)$_POST["id"];
        }

        if (empty($id)) {
            throw new AppException(AppException::MISSING_PARAMS);
        }

        $document = $this->selectCollection()->findOne([
            "_id" => new ObjectID($id)
        ]);

        if (empty($document)) {
            throw new AppException(AppException::NOT_FOUND);
        }

        return self::prettyPrint($document);
    }

    /**
     * Create document
     * @param string $json
     * @return bool|mixed
     * @throws AppException
     */
    public function create ($json = null)
    {
        if (empty($json)) {
            $json = (string)$_POST["document"];
        }

        if (empty($json)) {
            throw new AppException(AppException::MISSING_PARAMS);
        }

        $document = self::formatJSON($json);

        $result = $this->selectCollection()->insertOne($document);

        if ($result->isAcknowledged()) {
            return $result->getInsertedId();
        }
        return false;
    }

    /**
     * Edit document
     * @return bool
     * @throws AppException for missing params
     */
    public function edit ()
    {
        $json = (string)$_POST["document"];

        if (empty($json)) {
            throw new AppException(AppException::MISSING_PARAMS);
        }

        $document = self::formatJSON($json);

        $result = $this->selectCollection()->replaceOne([
            "_id" => $document->_id
        ], $document);

        return $result->isAcknowledged();
    }

    /**
     * Delete document
     * @return bool
     * @throws AppException for missing params
     */
    public function delete ()
    {
        $id = (string)$_POST["id"];

        if (empty($id)) {
            throw new AppException(AppException::MISSING_PARAMS);
        }

        $result = $this->selectCollection()->deleteOne([
            "_id" => new ObjectID($id)
        ]);

        return $result->isAcknowledged();
    }

    /**
     * @param string $json
     * @return array
     */
    public static function formatJSON ($json)
    {
        // try to make a valid json
        $json = preg_replace("/(,|\[|\{)\n(\t*)(\s*)/i", "$1", $json);
        $json = preg_replace("/([{,])([_a-zA-Z$][^: ]+):/", "$1\"$2\":", $json);
        $json = trim($json);

        $bson = \MongoDB\BSON\fromJSON($json);
        $document = \MongoDB\BSON\toPHP($bson, []);

        return $document;
    }

    /**
     * Converts BSONDocument to json pretty
     * @param BSONDocument|\stdClass $document
     * @return string
     */
    public static function prettyPrint ($document)
    {
        if (gettype($document) != "BSONDocument") {
            $document = \MongoDB\BSON\fromPHP($document);
        }
        // i do this to make it pretty as Mongo BSON class doesnt have that option :(
        return json_encode(json_decode(\MongoDB\BSON\toJSON($document)), JSON_PRETTY_PRINT);
    }
}
