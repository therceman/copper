<?php


namespace Copper\Component\HTML;


use Copper\Handler\ArrayHandler;
use Copper\Handler\StringHandler;
use Copper\Handler\VarHandler;

/**
 * Class HTML
 * @package Copper\Component\HTML
 */
class HTML
{
    /**
     * @param string $content
     * @return HTMLElement
     */
    public static function script($content)
    {
        $el = new HTMLElement(HTMLElement::SCRIPT, true);

        $el->innerHTML($content);

        return $el;
    }

    /**
     * @param HTMLElement[]|HTMLElementGroup[] $trList
     * @param HTMLElement[]|HTMLElementGroup[] $thList
     * @param string|array|null $class
     *
     * @return HTMLElement
     */
    public static function table($thList = [], $trList = [], $class = null)
    {
        $el = new HTMLElement(HTMLElement::TABLE);

        foreach (ArrayHandler::merge($thList, $trList) as $row) {
            if ($row instanceof HTMLElementGroup)
                $el->addElementGroup($row);
            else
                $el->addElement($row);
        }

        return $el->class($class);
    }

    /**
     * @param $text
     *
     * @return HTMLElement
     */
    public static function title($text)
    {
        $el = new HTMLElement(HTMLElement::TITLE);

        $el->innerText($text);

        return $el;
    }

    /**
     * @param $html
     * @param string $tag
     * @return HTMLElement
     */
    public static function html($html, $tag = HTMLElement::DIV)
    {
        $el = new HTMLElement($tag);
        $el->innerHTML($html);
        return $el;
    }

    /**
     * @param $name
     * @param null $content
     *
     * @return HTMLElement
     */
    public static function meta($name, $content = null)
    {
        $el = new HTMLElement(HTMLElement::META);

        $el->setAttr('name', $name);
        $el->setAttr('content', $content);

        return $el;
    }

    /**
     * @param array $selectors
     *
     * @return HTMLElement
     */
    public static function style(array $selectors)
    {
        $html = '';

        foreach ($selectors as $selector => $value_list) {
            $start = $selector . ' {' . PHP_EOL;
            $middle = '';

            foreach ($value_list as $key => $value) {
                $middle .= "\t" . $key . ': ' . $value . ';' . PHP_EOL;
            }

            $end = '}';

            $html .= PHP_EOL . $start . $middle . $end;
        }

        $el = new HTMLElement(HTMLElement::STYLE);

        $el->innerHTML($html);

        return $el;
    }

    /**
     * @param string $text
     * @param null $class
     *
     * @return HTMLElement
     */
    public static function p($text = '', $class = null)
    {
        $el = new HTMLElement(HTMLElement::P);

        $el->innerText($text);

        return $el->class($class);
    }

    /**
     * @param string $text
     * @param string|null $for
     *
     * @return HTMLElement
     */
    public static function label($text = '', $for = null)
    {
        $el = new HTMLElement(HTMLElement::LABEL);

        $el->innerText($text)->setAttr('for', $for);

        return $el;
    }

    /**
     * @param string $text
     * @param string|integer|false $value
     *
     * @return HTMLElement
     */
    public static function option($text = '', $value = null)
    {
        $el = new HTMLElement(HTMLElement::OPTION);

        $el->innerText($text)->setAttr('value', ($value === null) ? '' : $value);

        return $el;
    }

    public static function button($text = '', $value = null)
    {
        $el = new HTMLElement(HTMLElement::BUTTON);

        $el->innerText($text)->setAttr('value', ($value === null) ? '' : $value);

        return $el;
    }

    public static function hr()
    {
        return new HTMLElement(HTMLElement::HR, false);
    }

    public static function h1($text = '')
    {
        $el = new HTMLElement(HTMLElement::H1);

        return $el->innerText($text);
    }

    public static function h2($text = '')
    {
        $el = new HTMLElement(HTMLElement::H2);

        return $el->innerText($text);
    }

    public static function h3($text = '')
    {
        $el = new HTMLElement(HTMLElement::H3);

        return $el->innerText($text);
    }

    public static function h4($text = '')
    {
        $el = new HTMLElement(HTMLElement::H4);

        return $el->innerText($text);
    }

    public static function h5($text = '')
    {
        $el = new HTMLElement(HTMLElement::H5);

        return $el->innerText($text);
    }

    public static function h6($text = '')
    {
        $el = new HTMLElement(HTMLElement::H6);

        return $el->innerText($text);
    }

    public static function span($text = '', $class = null)
    {
        $el = new HTMLElement(HTMLElement::SPAN);

        $el->class($class);

        return $el->innerText($text);
    }

    /**
     * @param string $text
     * @param int|null $colspan
     *
     * @return HTMLElement
     */
    public static function td($text = '', $colspan = null)
    {
        $el = new HTMLElement(HTMLElement::TD);

        $el->innerText($text)->setAttr('colspan', $colspan);

        return $el;
    }


    /**
     * @param HTMLElement[] $liList
     *
     * @return HTMLElement
     */
    public static function ul($liList = [])
    {
        $el = new HTMLElement(HTMLElement::UL);

        foreach ($liList as $li) {
            $el->addElement($li);
        }

        return $el;
    }

    /**
     * @param string $text
     *
     * @return HTMLElement
     */
    public static function li($text = '')
    {
        $el = new HTMLElement(HTMLElement::LI);

        return $el->innerText($text);
    }

    /**
     * @param string $text
     * @param int|null $colspan
     *
     * @return HTMLElement
     */
    public static function th($text = '', $colspan = null)
    {
        $el = new HTMLElement(HTMLElement::TH);

        $el->innerText($text)->setAttr('colspan', $colspan);

        return $el;
    }

    /**
     * @param string|null $src
     * @param string|null $alt
     *
     * @return HTMLElement
     */
    public static function img($src = null, $alt = null)
    {
        $el = new HTMLElement(HTMLElement::IMG, false);

        $el->setAttr('src', $src)->setAttr('alt', $alt);

        return $el;
    }

    /**
     * @param string $data
     * @param string $type
     *
     * @return HTMLElement
     */
    public static function object(string $data, string $type)
    {
        $el = new HTMLElement(HTMLElement::OBJECT);

        $el->setAttr('data', $data)->setAttr('type', $type);

        return $el;
    }

    public static function svg($useHref = null, $useId = null)
    {
        $hrefHash = null;

        if (StringHandler::has($useHref, '#'))
            $hrefHash = explode('#', $useHref)[1];

        if ($hrefHash !== null)
            $useId = $hrefHash;

        if ($useId !== null)
            $useHref = $useHref . '#' . $useId;

        $use = new HTMLElement('use');
        $use->setAttr('href', $useHref);

        $svg = new HTMLElement(HTMLElement::SVG);
        $svg->class($useId);
        $svg->addElement($use);

        return $svg;
    }

    /**
     * @param string|null $src
     *
     * @return HTMLElement
     */
    public static function objectSvg($src = null)
    {
        return self::object($src, 'image/svg+xml');
    }

    /**
     * @param string $href
     * @param string|int|null $text
     * @param bool $openInNewWindow
     *
     * @return HTMLElement
     */
    public static function a(string $href, $text = null, $openInNewWindow = false)
    {
        $el = new HTMLElement(HTMLElement::A);

        $el->setAttr('href', $href)->innerText(($text === null) ? $href : $text);

        if ($openInNewWindow !== false)
            $el->setAttr('target', '_blank');

        return $el;
    }

    /**
     * @param string|array|null $class
     * @param string|int|null $text
     *
     * @return HTMLElement
     */
    public static function div($class = null, $text = null)
    {
        $el = new HTMLElement(HTMLElement::DIV);

        $el->innerText($text);

        return $el->class($class);
    }

    /**
     * @param HTMLElement[] $tdList
     *
     * @return HTMLElement
     */
    public static function tr($tdList = [])
    {
        $el = new HTMLElement(HTMLElement::TR);

        foreach ($tdList as $td) {
            $el->addElement($td);
        }

        return $el;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return HTMLInput
     */
    public static function input($name = null, $value = null)
    {
        $el = new HTMLInput();

        $el->name($name)->value($value)->type('text');

        $el->autocomplete(false)->spellcheck(false);

        return $el;
    }

    /**
     * @param string $name
     * @param string $value
     * @return HTMLInput
     */
    public static function inputHidden($name = null, $value = null)
    {
        return HTML::input($name, $value)->type('hidden');
    }

    /**
     * @param string $name
     * @param string $value
     * @return HTMLInput
     */
    public static function inputEmail($name = null, $value = null)
    {
        return HTML::input($name, $value)->type('email');
    }

    /**
     * @param string $name
     * @param string $value
     * @return HTMLInput
     */
    public static function inputPassword($name = null, $value = null)
    {
        return HTML::input($name, $value)->type('password');
    }

    /**
     * @param string $name
     * @param string $value
     * @param string|null $step
     * @param int|null $min
     * @param int|null $max
     * @return HTMLInput
     */
    public static function inputNumber($name = null, $value = null, $step = null, $min = null, $max = null)
    {
        $input = HTML::input($name, $value)->type('number')->autocomplete()->spellcheck();

        $input->setAttr('step', $step)->setAttr('min', $min)->setAttr('max', $max);

        return $input;
    }

    /**
     * @param string $name
     * @param string|string[] $accept List of MIME types, e.g. image/png, image/jpeg
     * @return HTMLInput
     */
    public static function inputFile($name = null, $accept = [])
    {
        $type_list = (VarHandler::isArray($accept)) ? $accept : [$accept];

        $el = HTML::input($name, null)->type('file');

        $el->setAttr('accept', ArrayHandler::join($type_list));

        return $el;
    }

    /**
     * @param string $name
     * @return HTMLInput
     * @var bool $checked
     */
    public static function inputCheckbox($name = null, $checked = false)
    {
        $checkboxEl = HTML::input($name)->type('checkbox')->autocomplete()->spellcheck();

        if ($checked === true)
            $checkboxEl->setAttr('checked', true);

        return $checkboxEl;
    }

    /**
     * @param string $name
     * @param string|int $value
     * @param bool $checked
     * @return HTMLInput
     */
    public static function inputRadio(string $name, $value, $checked = false)
    {
        $radioEl = HTML::input($name)->type('radio')->value($value)->autocomplete()->spellcheck();

        if ($checked !== false)
            $radioEl->setAttr('checked', true);

        return $radioEl;
    }

    /**
     * Create select element from array
     * <hr>
     * <code>
     * - select(['apple', 'banana'], 'fruits', 'banana')
     * # <select name="fruits">
     * #    <option value="apple">apple</option>
     * #    <option value="banana" selected>banana</option>
     * # </select>
     *
     * - select(['A','B'], 'letters', 1, true)
     * # <select name="letters">
     * #    <option value="0">A</option>
     * #    <option value="1" selected>B</option>
     * # </select>
     * </code>
     *
     * @param string $name
     * @param array $options
     * @param string $selVal
     * @param false $useKeyAsValue
     *
     * @return HTMLSelect
     */
    public static function select(array $options, string $name = null, $selVal = "", $useKeyAsValue = false)
    {
        $el = new HTMLSelect();

        $el->options($options);
        $el->name($name);
        $el->value($selVal);
        $el->useKeyAsValue($useKeyAsValue);

        return $el;
    }

    /**
     * Create select element from collection
     * <hr>
     * <code>
     * - select([["id"=>1, "title"=>"A"], ["id"=>2, "title"=>"B"]], 'id', 'title', 'letters', 2)
     * # <select name="letters">
     * #    <option value="1">A</option>
     * #    <option value="2" selected>B</option>
     * # </select>
     * </code>
     *
     * @param string $name
     * @param array $options
     * @param string $valField
     * @param string $textField
     * @param string $selVal
     *
     * @return HTMLSelect
     */
    public static function selectCollection(array $options, string $valField, string $textField, string $name = null, $selVal = '')
    {
        $customOptions = [];

        foreach ($options as $key => $val) {
            $val = (array)$val;
            $customOptions[$val[$valField]] = $val[$textField];
        }

        return static::select($customOptions, $name, $selVal, true);
    }

    /**
     * Create textarea element
     *
     * @param string $name
     * @param string|null $text
     *
     * @return HTMLElement
     */
    public static function textarea($name = null, $text = '')
    {
        $el = new HTMLElement(HTMLElement::TEXTAREA);

        $el->name($name)->innerText($text);

        return $el;
    }

    /**
     * Create form element with POST method
     *
     * @param string $action
     *
     * @return HTMLElement
     */
    public static function form(string $action)
    {
        $el = new HTMLElement(HTMLElement::FORM);

        $el->setAttr('action', $action)->setAttr('method', 'post');

        return $el;
    }

    /**
     * Create form element with GET method
     *
     * @param string $action
     *
     * @return HTMLElement
     */
    public static function formGet(string $action)
    {
        return HTML::form($action)->setAttr('method', 'get');
    }

    /**
     * Create form element for file upload
     *
     * @param $action
     *
     * @return HTMLElement
     */
    public static function formUpload($action)
    {
        return HTML::form($action)->setAttr('enctype', 'multipart/form-data');
    }

    // ------------- HTML Groups ------------------

    /**
     * Create group of elements (with div wrapper by default)
     * <hr>
     * <code>
     * // Basic Usage
     * - group([HTML::img('a.png'), HTML::img('b.png')], 'icons')
     * # <div class="icons">
     * #    <img src="a.png">
     * #    <img src="b.png">
     * # </div>
     *
     * // Custom tag & dynamic class (using findAllElements method)
     * - group([HTML::a('/users'), HTML::a('/posts')], 'links', 'nav')
     *    ->findAllElements('a', function (HTMLElement $el) {
     *        return $el->toggleClass('active', $el->getAttr('href') === '/users');
     *    })
     * # <nav class="links">
     * #    <a href="/users" class="active">/users</a>
     * #    <a href="/posts">/posts</a>
     * # </nav>
     * </code>
     *
     * @param HTMLElement[] $array
     * @param bool $useWrapper
     * @param string $tag
     * @param string $class
     *
     * @return HTMLGroup
     */
    public static function group(array $array, string $class = null, $tag = HTMLElement::DIV, bool $useWrapper = true)
    {
        $group = new HTMLGroup($array);

        $group->useWrapper($useWrapper);
        $group->tag($tag);
        $group->class($class);

        return $group->build();
    }

    /**
     * @param string $label
     * @param string|null $name
     * @param string|int|null $value
     * @param bool $checked
     * @param string|null $id
     *
     * @return HTMLRadioGroup
     */
    public static function radioGroup(string $label, string $name, $value = null, $checked = false, $id = null)
    {
        $group = new HTMLRadioGroup($label, $name);

        $group->value($value);
        $group->checked($checked);
        $group->id($id);

        return $group->build();
    }

    /**
     * @param string|false $label
     * @param false $checked
     * @param string $name
     * @param false $id
     * @param bool $falseValue
     *
     * @return HTMLCheckboxGroup
     */
    public static function checkboxGroup(string $label, $checked = false, string $name = null, $id = null, $falseValue = false)
    {
        $group = new HTMLCheckboxGroup($label);

        $group->checked($checked);
        $group->name($name);
        $group->id($id);
        $group->falseValue($falseValue);

        return $group->build();
    }
}