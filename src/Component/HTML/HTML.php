<?php


namespace Copper\Component\HTML;


class HTML
{
    /**
     * @param string $text
     *
     * @return HTMLElement
     */
    public static function p($text = '')
    {
        $el = new HTMLElement('p');

        $el->innerText($text);

        return $el;
    }

    /**
     * @param string $text
     * @param boolean $for
     *
     * @return HTMLElement
     */
    public static function label($text = '', $for = null)
    {
        $el = new HTMLElement('label');

        $el->innerText($text);

        $el->setAttr('for', $for);

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
        $el = new HTMLElement('option');

        $el->innerText($text);

        $el->setAttr('value', ($value === null) ? '' : $value);

        return $el;
    }

    public static function button($text = '')
    {
        $el = new HTMLElement('button');

        $el->innerText($text);

        return $el;
    }

    public static function span($text = '')
    {
        $el = new HTMLElement('span');

        $el->innerText($text);

        return $el;
    }

    /**
     * @param string $text
     * @param int|null $colspan
     *
     * @return HTMLElement
     */
    public static function td($text = '', $colspan = null)
    {
        $el = new HTMLElement('td');

        $el->innerText($text);
        $el->setAttr('colspan', $colspan);

        return $el;
    }


    /**
     * @param HTMLElement[] $liList
     *
     * @return HTMLElement
     */
    public static function ul($liList = [])
    {
        $el = new HTMLElement('ul');

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
        $el = new HTMLElement('li');

        $el->innerText($text);

        return $el;
    }

    /**
     * @param string $text
     * @param int|null $colspan
     *
     * @return HTMLElement
     */
    public static function th($text = '', $colspan = null)
    {
        $el = new HTMLElement('th');

        $el->innerText($text);
        $el->setAttr('colspan', $colspan);

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
        $el = new HTMLElement('img');

        $el->setAttr('src', $src);
        $el->setAttr('alt', $alt);

        return $el;
    }

    /**
     * @param string $href
     * @param string|null $text
     *
     * @return HTMLElement
     */
    public static function link(string $href, $text = null)
    {
        $el = new HTMLElement('a');

        $el->setAttr('href', $href);
        $el->innerText(($text !== null) ? $text : $href);

        return $el;
    }

    /**
     * @param string|null $class
     *
     * @return HTMLElement
     */
    public static function div($class = null)
    {
        $el = new HTMLElement('div');

        $el->setAttr('class', $class);

        return $el;
    }

    /**
     * @param HTMLElement[] $tdList
     *
     * @return HTMLElement
     */
    public static function tr($tdList = [])
    {
        $el = new HTMLElement('tr');

        foreach ($tdList as $td) {
            $el->addElement($td);
        }

        return $el;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return HtmlInputElement
     */
    public static function input($name = null, $value = null)
    {
        $el = new HtmlInputElement();

        $el->name($name);
        $el->value($value)->type('text');
        $el->autocomplete(false)->spellcheck(false);

        return $el;
    }

    /**
     * @param string $name
     * @param string $value
     * @return HtmlInputElement
     */
    public static function inputHidden($name = null, $value = null)
    {
        return HTML::input($name, $value)->type('hidden');
    }

    /**
     * @param string $name
     * @param string $value
     * @return HtmlInputElement
     */
    public static function inputEmail($name = null, $value = null)
    {
        return HTML::input($name, $value)->type('email');
    }

    /**
     * @param string $name
     * @param string $value
     * @return HtmlInputElement
     */
    public static function inputPassword($name = null, $value = null)
    {
        return HTML::input($name, $value)->type('password');
    }

    /**
     * @param string $name
     * @param string $value
     * @return HtmlInputElement
     */
    public static function inputNumber($name = null, $value = null)
    {
        return HTML::input($name, $value)->type('number');
    }

    /**
     * @param string $name
     * @param string $value
     * @return HtmlInputElement
     */
    public static function inputFile($name = null, $value = null)
    {
        return HTML::input($name, $value)->type('file');
    }

    /**
     * @param string $name
     * @param boolean $checked
     * @return HtmlInputElement
     */
    public static function inputCheckbox($name = null, $checked = false)
    {
        $checkboxEl = HTML::input($name)->type('checkbox');

        if ($checked !== false)
            $checkboxEl->setAttr('checked', true);

        return $checkboxEl;
    }

    /**
     * @param string $name
     * @param string|int $value
     * @param boolean $checked
     * @return HtmlInputElement
     */
    public static function inputRadio($name, $value, $checked = false)
    {
        $radioEl = HTML::input($name)->type('radio')->value($value);

        if ($checked !== false)
            $radioEl->setAttr('checked', true);

        return $radioEl;
    }

    /**
     *  HTML::select('banana', ['apple', 'pie'], false, true)
     *
     * - option value="apple">apple | option value="pie">pie
     *
     *
     * HTML::select('bingo', ['a' => 'aaaa', 'b' => 'bbbbb', 'c' => 'ccccc'], 'b')
     *
     * - option value="a">aaaa | option value="b" selected>bbbbb | option value="c">ccccc
     *
     * @param string $name
     * @param array $options
     * @param string $selVal
     * @param false $useTextAsValue
     *
     * @return HTMLElement
     */
    public static function select(array $options, string $name = null, $selVal = "", $useTextAsValue = false)
    {
        $el = new HTMLElement('select');

        $el->name($name);

        foreach ($options as $value => $text) {
            $optionEl = new HTMLElement('option');

            $optionEl->setAttr('value', ($useTextAsValue) ? $text : $value);
            $optionEl->innerHTML($text);

            if ($useTextAsValue && $selVal == $text || $useTextAsValue === false && $selVal == $value)
                $optionEl->setAttr('selected', true);

            $el->addElement($optionEl);
        }

        return $el;
    }

    /**
     * Accepts $options as collection, e.g. [["id" => 1, "name" => "alfa"]]
     *
     * @param string $name
     * @param array $options
     * @param string $valField
     * @param string $textField
     * @param string $selVal
     *
     * @return HTMLElement
     */
    public static function selectCollection(array $options, string $valField, string $textField, string $name = null, $selVal = '')
    {
        $customOptions = [];

        foreach ($options as $key => $val) {
            $val = (array)$val;
            $customOptions[$val[$valField]] = $val[$textField];
        }

        return static::select($customOptions, $name, $selVal);
    }

    /**
     * @param string $name
     * @param string $text
     *
     * @return HTMLElement
     */
    public static function textarea($name = null, string $text = '')
    {
        $el = new HTMLElement('textarea');

        $el->name($name)->innerText($text);

        return $el;
    }

    public static function form(string $action)
    {
        $el = new HTMLElement('form');

        $el->setAttr('action', $action);
        $el->setAttr('method', 'post');

        return $el;
    }

    public static function formGet(string $action)
    {
        return HTML::form($action)->setAttr('method', 'get');
    }

    public static function formUpload($action)
    {
        return HTML::form($action)->setAttr('enctype', 'multipart/form-data');
    }

}