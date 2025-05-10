<?php

declare(strict_types=1);

namespace Framework;


class Paginator
{

    /**
     * The total number of pages
     * @var integer
     */
    protected int|float $total_pages;

    /**
     * The current page, filtered
     * @var integer
     */
    protected mixed $page;

    /**
     * The starting record (SQL OFFSET)
     * @var integer
     */
    protected int|float $offset;

    /**
     * Class constructor
     *
     * @param integer $total_records Total number of records
     * @param integer $records_per_page Number of records on each page
     * @param string $page Current page
     *
     * @return void
     */

//    public function __construct(int $total_records, int $records_per_page, string $page)
    public function __construct(int $total_records, int $records_per_page, int|string $page)
    {
        $this->total_pages = ceil($total_records / $records_per_page);

        // Make sure the page number is within a valid range from 1 to the total number of pages
        $data = [
            'options' => [
                'default'   => 1,
                'min_range' => 1,
                'max_range' => $this->total_pages
            ]
        ];

        $this->page = filter_var($page, FILTER_VALIDATE_INT, $data);

        // Calculate the starting record based on the page and number of records per page
        $this->offset = $records_per_page * ($this->page - 1);
    }

    /**
     * Get the starting record (SQL OFFSET)
     *
     * @return integer
     */
    public function getOffset(): float|int
    {
        return $this->offset;
    }

    /**
     * Get the current page
     *
     * @return integer
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Get the total number of pages
     *
     * @return integer
     */
    public function getTotalPages(): float|int
    {
        return $this->total_pages;
    }
}
