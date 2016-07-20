<?php

namespace App\Controllers;

use App\System\AppException;
use App\System\Controller;

class Index extends Controller
{
    /**
     * Create index
     * @throws AppException
     */
    public function create ()
    {
        $json = (string)$_POST["index"];

        if (empty($json)) {
            throw new AppException(AppException::MISSING_PARAMS);
        }
        $result = $this->selectCollection()->createIndex($json);

        var_dump($result);die;
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

        return $this->render('collection/index_list.html.twig', [
            "indexes" => $list
        ]);
    }
}
