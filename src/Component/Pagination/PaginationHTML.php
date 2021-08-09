<?php


namespace Copper\Component\Pagination;


use Copper\Component\HTML\HTML;
use Copper\Component\HTML\HTMLElement;
use Copper\Component\HTML\HTMLGroup;
use Copper\Handler\StringHandler;

/**
 * Class PaginationHTML
 * @package Copper\Component\Pagination
 */
class PaginationHTML
{
    const DEFAULT_CLASS = 'pagination';
    const DEFAULT_HREF_FORMAT = '?page=%d';
    const PREV_PAGE_SIGN = '‹';
    const NEXT_PAGE_SIGN = '›';

    /** @var PaginationEntity */
    private $entity;
    /** @var string|null */
    private $class;
    /** @var HTMLElement|null */
    private $style;
    /** @var HTMLGroup */
    private $group;
    /** @var \Closure|null */
    private $hrefMap;

    /**
     * PaginationHTML constructor.
     *
     * @param $entity
     * @param \Closure|null $hrefMap
     * @param string $class
     */
    public function __construct($entity, $hrefMap = null, $class = self::DEFAULT_CLASS)
    {
        $this->entity = $entity;
        $this->hrefMap = $hrefMap;
        $this->class = $class;
        $this->style = $this->createStyle();
        $this->group = $this->createGroup();
    }

    /**
     * @return HTMLElement
     */
    private function createStyle()
    {
        return HTML::style([
            '.pagination' => [
                'border' => '1px solid #ccc',
                'border-radius' => '6px',
                'margin-right' => '5px',
                'padding' => '6px 3px',
                'margin-top' => '15px',
                'text-align' => 'center',
            ],
            '.pagination a' => [
                'width' => '16px',
                'border-radius' => '6px',
                'display' => 'inline-block',
                'text-align' => 'center',
                'padding' => '6px 8px',
                'color' => '#707070',
            ],
            '.pagination a.active' => [
                'background' => '#10ae3f',
                'color' => '#fff',
            ],
            '.pagination .arrow' => [
                'font-size' => '33px',
                'vertical-align' => 'sub',
                'line-height' => '0'
            ]
        ]);
    }


    /**
     * @param int $page
     *
     * @return string
     */
    private function createHref(int $page)
    {
        return ($this->hrefMap !== null)
            ? call_user_func_array($this->hrefMap, [$page])
            : StringHandler::sprintf(self::DEFAULT_HREF_FORMAT, [$page]);
    }

    /**
     * @return HTMLGroup
     */
    private function createGroup()
    {
        $prev_page = HTML::a($this->createHref($this->entity->prev_page), self::PREV_PAGE_SIGN)
            ->class('arrow prev')->toggle($this->entity->prev_page !== false);

        $page_list = [];
        for ($i = 1; $i < $this->entity->page_count + 1; $i++) {
            $page_list[] = HTML::a($this->createHref($i), $i)
                ->toggleClass('active', $i === $this->entity->page_num);
        }

        $next_page = HTML::a($this->createHref($this->entity->next_page), self::NEXT_PAGE_SIGN)
            ->class('arrow next')->toggle($this->entity->next_page !== false);

        return HTML::group([
            $prev_page,
            $page_list,
            $next_page,
            $this->style
        ], 'pagination ' . $this->class);
    }

    /**
     * @return HTMLGroup
     */
    public function getGroup(): HTMLGroup
    {
        return $this->group;
    }

}