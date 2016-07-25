<?php

namespace App\Controllers;

use App\System\AppException;
use App\System\Controller;

class Index extends Controller
{
    /**
     * https://docs.mongodb.com/manual/reference/command/createIndexes/
     * Create index
     * @throws AppException
     */
    public function create ()
    {
        $fields = $_POST["fields"];
        $name = $_POST["name"];
        $unique = $_POST["unique"];
        $options = [];

        if (empty($fields)) {
            throw new AppException(AppException::MISSING_PARAMS);
        }

        if (!empty($name)) {
            $options["name"] = $name;
        }

        // only applies to ASC & DESC indexes
        if (!empty($unique) && $unique == "true") {
            $options["unique"] = $unique;
        }

        foreach ($fields as $k => &$v) {
            if (is_numeric($v)) {
                $v = (int)$v;
            }
        }

        // OnSuccess: Will return the index name
        // OnFail: An array
        $result = $this->selectCollection()->createIndex($fields, $options);

        if (is_string($result)) {
            return true;
        }

        return $result;
    }

    /**
     * Delete index
     */
    public function delete ()
    {
        $name = $_POST["name"];

        if (empty($name)) {
            throw new AppException(AppException::MISSING_PARAMS);
        }

        $result = $this->selectCollection()->dropIndex($name);

        return ($result && $result["ok"]);
    }

    /**
     * Show current collection indexes
     * @return mixed
     * @throws AppException
     */
    public function showList ()
    {
        $cursor = $this->selectCollection()->listIndexes();
        $list = [];

        foreach ($cursor as $index)
        {
            $type = "normal";

            if ($index->isUnique()) {
                $type = "unique";
            } else if ($index->isSparse()) {
                $type = "sparse";
            } else if ($index->isTtl()) {
                $type = "ttl";
            }

            $list[] = [
                //"isUnique" => $index->isUnique(),
                //"isSparse" => $index->isSparse(),
                //"isTTL" => $index->isTtl(),
                "name" => $index->getName(),
                "type" => $type,
                "fields" => json_encode($index->getKey(), JSON_PRETTY_PRINT)
            ];
        }

        // get document keys
        $document = $this->selectCollection()->findOne([]);
        $keys = [];

        if ($document && is_array($document)) {
            $keys = array_keys($document);
        }

        return $this->render('collection/index_list.html.twig', [
            "indexes" => $list,
            "documentKeys" => $keys
        ]);
    }
}
