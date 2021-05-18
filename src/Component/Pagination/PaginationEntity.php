<?php


namespace Copper\Component\Pagination;


use Copper\Component\HTML\HTMLGroup;

/**
 * Class PaginationEntity
 * @package Copper\Component\Pagination
 */
class PaginationEntity
{
    /** @var int */
    public $page_count;
    /** @var int */
    public $page_num;
    /** @var int */
    public $page_item_count;
    /** @var int|false */
    public $prev_page;
    /** @var int|false */
    public $next_page;
    /** @var int */
    public $item_count;
    /** @var int */
    public $item_count_from;
    /** @var int */
    public $item_count_to;

    /**
     * @param \Closure|null $hrefMap
     * @param string $class
     * @return HTMLGroup
     */
    public function createHTMLGroup($hrefMap = null, $class = PaginationHTML::DEFAULT_CLASS)
    {
        return (new PaginationHTML($this, $hrefMap, $class))->getGroup();
    }
}