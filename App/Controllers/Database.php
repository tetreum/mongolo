<?php

namespace App\Controllers;

use App\System\App;
use App\System\AppException;
use App\System\Controller;

class Database extends Controller
{
    /**
     * Rename database
     * @return \MongoDB\Driver\Cursor
     * @throws AppException
     */
    public function rename ()
    {
        $db = strip_tags($_POST["db"]);
        $newName = strip_tags($_POST["new"]);

        if (empty($db) || empty($newName) || $newName == $db) {
            throw new AppException(AppException::MISSING_PARAMS);
        }

        $result = $this->selectDB()->command([
            "renameCollection" => "$db",
            "to" => "$newName"
        ]);

        return $result;
    }

    /**
     * Get available dbs
     * @return array
     */
    public function getList ()
    {
        $list = [];

        if (empty($this->container->get("settings")["mongo"]["db"])) {
            $dbs = $this->mongo->connection()->listDatabases();

            foreach ($dbs as $db) {
                $list[] = $db->getName();
            }
        } else {
            $list[] = $this->container->get("settings")["mongo"]["db"];
        }
        sort($list);

        return $list;
    }

    /**
     * List collections from given db
     * @return array
     * @throws AppException
     */
    public function getCollections ()
    {
        $list = [];
        $db = strip_tags($_POST["db"]);

        if (empty($db)) {
            throw new AppException(AppException::MISSING_PARAMS);
        }

        $collections = $this->mongo->selectDB($db)->listCollections();

        foreach ($collections as $collection)
        {
            if ($collection->getName() == "system.indexes") {
                continue;
            }
            $list[] = $collection->getName();
        }
        sort($list);

        return $list;
    }

    /**
     * Display server info
     * @return mixed
     */
    public function showServerInfo ()
    {
        /*
        // get any db
        $db = $this->getList()[0];
        $info = $this->mongo->selectDB($db)->command([
            'buildinfo' => 1,
            // 'serverStatus' => 1
            // 'dbStats' => 1
        ]);
        */

        return $this->render('server_info.html.twig', [
            "mongoloInfo" => App::info()
        ]);
    }
}