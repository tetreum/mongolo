<?php
namespace App\System;

/**
 * Mongo functions Wrapper
 */
class MongoWrapper
{
    public static $i = 0; // to count the query amount

    // connection vars
    private $server;
    private $dbname;
    private $options;

    // mongoDB objects
    /**
     * @var \MongoDB\Client
     */
    protected $connection = false;

    /**
     * @var \MongoDB\Database
     */
    private $db;
    private $collection;

    public function __construct(array $data)
    {
        if (!isset($data["options"])) {
            $data["options"] = [];
        }

        $this->server = $data["ip"];
        $this->dbname = $data["db"];
        $this->options = $data["options"];
    }

    /**
     * Returns current connection link
     * @return \MongoDB\Client
     */
    public  function connection ()
    {
        return $this->init()->connection;
    }

    /**
     * Builds connection string
     * @return string
     */
    private function buildConnectionString ()
    {
        $authLine = "";
        if (isset($_SESSION["user"]["password"])) {
            $authLine = $_SESSION["user"]["name"] .":". $_SESSION["user"]["password"] . "@";
        }

        return "mongodb://" . $authLine . $this->server;
    }

    /**
     * Starts mongo connection
     * @return $this
     */
    public function init()
    {
        if ($this->connection) {return $this;}

        $this->connection = new \MongoDB\Client($this->buildConnectionString(), $this->options);

//        $this->db = $this->selectDB($this->dbname);

        return $this;
    }

    /**
     * Checks if app can connect to mongo
     * @param string $user
     * @param string $pass
     * @return bool
     */
    public function canConnect ($user = null, $pass = null)
    {
        // check if mongo authentication is required
        $authLine = "";
        if (!empty($user)) {
            $authLine = "$user:$pass@";
        }
        try {
            $client = new \MongoDB\Client("mongodb://".$authLine . $this->server, $this->options);

            if (!empty($this->dbname)) {
                $client->selectDatabase($this->dbname)->listCollections();
            } else {
                $client->listDatabases();
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Returns current selected db
     * @return \MongoDB\Database
     */
    public function currentDB ()
    {
        return $this->init()->db;
    }

    /**
     * @param $name
     * @return \MongoDB\Database
     */
    public function selectDB($name)
    {
        return $this->connection()->selectDatabase($name);
    }

    /**
     * Selects a collections
     * @param string $collection
     * @return $this
     */
    public function selectCollection($collection)
    {
        $this->collection = $this->init()->db->selectCollection($collection);
        return $this;
    }

    /**
     * Makes a find query
     * http://php.net/manual/en/mongodb-driver-query.construct.php#refsect1-mongodb-driver-query.construct-examples]
     * @param string $db
     * @param string $collection
     * @param array $where
     * @param array $options
     * @return \MongoDB\Driver\Cursor
     */
    public function find($db, $collection, $where = [], $options = [])
    {
        $query = new \MongoDB\Driver\Query($where, $options);

        $manager = new \MongoDB\Driver\Manager($this->buildConnectionString());
        $readPreference = new \MongoDB\Driver\ReadPreference(\MongoDB\Driver\ReadPreference::RP_PRIMARY);
        return $manager->executeQuery("$db.$collection", $query, $readPreference);
    }
}
