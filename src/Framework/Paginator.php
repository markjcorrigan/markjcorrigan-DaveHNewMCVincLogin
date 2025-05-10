<?php

declare(strict_types=1);

namespace Framework;


class Paginator
{

    protected float $total_pages;


    protected mixed $page;


    protected int|float $offset;


    public function __construct($total_records, $records_per_page, $page)
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


    public function getOffset(): float|int
    {
        return $this->offset;
    }

    public function getPage(): int
    {
        return $this->page;
    }


    public function getTotalPages(): float
    {
        return $this->total_pages;
    }
}
