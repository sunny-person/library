<?php


namespace App\UI;


class Pagination {

    private $currentPage;

    private $totalPageCount;

    private $itemsPerPage;

    public function __construct(int $currentPage, int $itemsCount, int $itemsPerPage) {
        $this->currentPage = $currentPage;
        $this->itemsPerPage = $itemsPerPage;
        $this->totalPageCount = (int) ceil($itemsCount / $itemsPerPage);
    }

    public function hasNextPage(): bool {
        return $this->currentPage < $this->totalPageCount;
    }

    public function getOffset(): int {
        if ($this->currentPage <= 1) {
            return 0;
        }

        return $this->itemsPerPage * ($this->currentPage - 1);
    }

    public function getLimit(): int {
        return $this->itemsPerPage;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int {
        return $this->currentPage;
    }

    /**
     * @return false|float
     */
    public function getTotalPageCount() {
        return $this->totalPageCount;
    }

}