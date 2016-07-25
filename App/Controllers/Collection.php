<?php

namespace App\Controllers;

use App\System\AppException;
use App\System\Controller;
use App\System\Pagination;

class Collection extends Controller
{
    const DEFAULT_LIMIT = 25;

    /**
     * Show collection create form
     * @return mixed
     */
    public function showCreate ()
    {
        return $this->render("collection/create.html.twig");
    }

    /**
     * Create collection
     * @return bool
     * @throws AppException
     */
    public function create ()
    {
        $collection = (string)$_POST["name"];

        if (empty($collection)) {
            throw new AppException(AppException::MISSING_PARAMS);
        }

        $result = $this->selectDB()->createCollection($collection);

        if ($result) {
            return true;
        }

        return false;
    }

    /**
     * Rename collection
     * @return \MongoDB\Driver\Cursor
     * @throws AppException
     */
    public function rename ()
    {
        $db = (string)$_POST["db"];
        $collection = (string)$_POST["current"];
        $newName = (string)$_POST["new"];

        if (empty($db) || empty($collection) || empty($newName) || $newName == $collection) {
            throw new AppException(AppException::MISSING_PARAMS);
        }

        $result = $this->selectDB()->command(array(
            "renameCollection" => "$db.$collection",
            "to" => "$db.$newName"
        ));

        return $result;
    }

    /**
     * Delete collection
     *
     * @return bool
     * @throws AppException
     */
    public function drop ()
    {
        $result = $this->selectCollection()->drop();

        return ($result && $result["ok"]);
    }

    /**
     * Deletes all entries without removing the collection
     *
     * @return bool
     * @throws AppException
     */
    public function truncate ()
    {
        $result = $this->selectCollection()->deleteMany([]);

        return $result->isAcknowledged();
    }

    /**
     * Show collection entries & query form (collection homepage)
     * @return mixed
     * @throws AppException
     */
    public function showEntries ()
    {
        $sampleDocument = [];
        $results = $this->doQuery();

        // extract document keys to use them in query autocompletion
        if (isset($results[0])) {
            $sampleDocument = json_decode($results[0]["document"], true);
        }

        return $this->render("collection/home.html.twig", [
            "results" => $results['results'],
            "pagination" => $results['pagination'],
            "db" => $_REQUEST["db"],
            "collection" => $_REQUEST["collection"],
            "autocompleteFields" => array_keys($sampleDocument)
        ]);
    }

    /**
     * Execute & show ajax query result
     * @throws AppException
     */
    public function showQueryResult ()
    {
        switch ($_GET["action"])
        {
            case "insert":
                // @ToDo: Decide how to output the result
                $document = new Document(null, $this->response);

                if ($documentId = $document->create($_GET["query"]))
                {
                    return $this->render("collection/single_result.html.twig", [
                        "entry" => [
                            "id" => $documentId,
                            "document" => $document->get($documentId),
                        ]
                    ]);
                } else {
                    throw new AppException(AppException::ACTION_FAILED);
                }
                break;
            case "modify":
                $where = $_REQUEST["query"];
                $changes = $_REQUEST["changes_query"];

                if (empty($where) || empty($changes)) {
                    throw new AppException(AppException::MISSING_PARAMS);
                }

                $where = Document::formatJSON($where);
                $changes = Document::formatJSON($changes);

                $result = $this->selectCollection()->updateMany($where, $changes);

                echo  _("Affected rows") . ": " . $result->getModifiedCount();
                return;
                break;
            case "remove":
                $where = $_REQUEST["query"];

                if (empty($where)) {
                    throw new AppException(AppException::MISSING_PARAMS);
                }

                $where = Document::formatJSON($where);

                $result = $this->selectCollection()->deleteMany($where);

                echo  _("Affected rows") . ": " . $result->getDeletedCount();
                return;
                break;
            case "find":
            default:
                $results = $this->doQuery();
                break;
        }

        return $this->render("collection/query_result.html.twig", $results);
    }

    public function doQuery ()
    {
        if (isset($_GET["query"]) && !empty($_GET["query"])) {
            $query = Document::formatJSON($_GET["query"]);
        } else {
            $query = [];
        }

        $db = (string)$_REQUEST["db"];
        $collection = (string)$_REQUEST["collection"];
        $sort = $_GET["sort"];
        $limit = (int)$_GET["limit"];
        $page = (int)$_GET["page"];

        if (empty($db) || empty($collection)) {
            throw new AppException(AppException::MISSING_PARAMS);
        }

        if ($limit < 1) {
            $limit = self::DEFAULT_LIMIT;
        }

        $options = [
            "limit" => $limit,
        ];
        
        if (empty($sort)) {
            $sort = ["_id" => -1];
        }

        if (is_array($sort)) {
            foreach ($sort as $k => &$v) {
                $v = (int)$v;
            }
            $options['sort'] = $sort;
        }

        if ($page > 1) {
            $options['skip'] = $page * $limit;
        }

        $cursor = $this->mongo->find($db, $collection, $query, $options);
        $results = [];

        foreach ($cursor as $result)
        {
            $results[] = [
                "id" => $result->_id,
                "document" => Document::prettyPrint($result)
            ];
        }

        $page = $page + 1;
        $totalResults = $this->count($db, $collection, $query);
        $pagination = new Pagination($page, $limit, $totalResults);

        return [
            'results' => $results,
            'pagination' => [
                'totalPages' => $pagination->totalPages(),
                'currentPage' => $page,
                'perPage' => $limit,
                'totalResults' => $totalResults
            ]
        ];
    }

    protected function count($db, $collection, $query) {
        return $this->mongo->selectDB($db)->selectCollection($collection)->count($query);
    }
}
