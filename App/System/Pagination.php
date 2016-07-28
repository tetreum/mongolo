<?php

namespace App\System;

/**
 * Class Pagination
 * @package App\System
 */
class Pagination {

    protected $currentPage;
    protected $perPage; // number of items shown per page
    protected $totalCount; // total number of items to be displayed

    /**
     * Pagination constructor.
     * @param int $page
     * @param int $perPage
     * @param int $totalCount
     */
    public function __construct($page = 1, $perPage = 20, $totalCount = 0)
    {
        $this->currentPage = (int) $page;
        $this->perPage = (int) $perPage;
        $this->totalCount = (int) $totalCount;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @return int
     */
    public function offset()
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    /**
     * @return float
     */
    public function totalPages()
    {
        return ceil($this->totalCount / $this->perPage);
    }

    /**
     * @return int
     */
    public function previous()
    {
        return $this->currentPage - 1;
    }

    /**
     * @return int
     */
    public function next()
    {
        return $this->currentPage + 1;
    }

    /**
     * @return bool
     */
    public function hasPrevious()
    {
        return $this->previous() >= 1 ? true : false;
    }

    /**
     * @return bool
     */
    public function hasNext()
    {
        return $this->next() <= $this->totalPages() ? true : false;
    }

}
