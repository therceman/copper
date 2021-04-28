<?php


namespace Copper\Component\Pagination;


use Copper\Handler\NumberHandler;

/**
 * Class Pagination
 * @package Copper\Component\Pagination
 */
class Pagination
{
    /**
     * @param int $item_count
     * @param int $page_item_count
     * @return float|int
     */
    public static function calcPageCount(int $item_count, int $page_item_count)
    {
        $page_count = NumberHandler::round($item_count / $page_item_count, 0);

        return ($page_count * $page_item_count) > $item_count ? $page_count : $page_count + 1;
    }

    /**
     * @param int $page_num
     * @param int $page_item_count
     * @return int
     */
    public static function calcItemCountFrom(int $page_num, int $page_item_count)
    {
        return ($page_num > 1) ? ($page_item_count * ($page_num - 1)) + 1 : 1;
    }

    /**
     * @param int $from
     * @param int $page_item_count
     * @param int|false $item_count
     * @return float|int|mixed
     */
    public static function calcItemCountTo(int $from, int $page_item_count, $item_count)
    {
        $count = $from + $page_item_count - 1;

        return ($count > $item_count) ? $item_count : $count;
    }

    /**
     * @param int $page_num
     * @return false|int
     */
    public static function getPrevPage(int $page_num)
    {
        return $page_num > 1 ? $page_num - 1 : false;
    }

    /**
     * @param int $page_num
     * @param int $page_count
     * @return false|int
     */
    public static function getNextPage(int $page_num, int $page_count)
    {
        return $page_num < $page_count ? $page_num + 1 : false;
    }

    /**
     * @param int|string $item_count
     * @param int|string $page_item_count
     * @param int|string $page_num
     *
     * @return PaginationEntity
     */
    public static function create($item_count, $page_item_count, $page_num)
    {
        $item_count = intval($item_count);
        $page_item_count = intval($page_item_count);
        $page_num = intval($page_num);

        $pagination = new PaginationEntity();

        $pagination->item_count = $item_count;
        $pagination->page_item_count = $page_item_count;
        $pagination->page_num = $page_num;

        $pagination->page_count = self::calcPageCount($item_count, $page_item_count);
        $pagination->prev_page = self::getPrevPage($page_num);
        $pagination->next_page = self::getNextPage($page_num, $pagination->page_count);
        $pagination->item_count_from = self::calcItemCountFrom($page_num, $page_item_count);
        $pagination->item_count_to = self::calcItemCountTo($pagination->item_count_from, $page_item_count, $item_count);

        return $pagination;
    }
}