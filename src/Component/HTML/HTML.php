<?php


namespace Copper\Component\HTML;


class HTML
{
    /**
     * @param string $text
     * @param boolean $for
     *
     * @return HTMLElement
     */
    public static function label(string $text, $for = false)
    {
        $el = new HTMLElement('label');

        $el->innerText($text);

        if ($for !== false)
            $el->setAttr('for', $for);

        return $el;
    }

    public static function button($text) {
        $el = new HTMLElement('button');

        $el->innerText($text);

        return $el;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return HTMLElement
     */
    public static function inputText(string $name, $value = '')
    {
        $el = new HTMLElement('input', true);

        $el->name($name)->setAttr('value', $value)->setAttr('type', 'text');

        return $el;
    }

    /**
     * @param string $name
     * @param string $value
     * @return HTMLElement
     */
    public static function inputHidden(string $name, $value = '')
    {
        return HTML::inputText($name, $value)->setAttr('type', 'hidden');
    }

    /**
     * @param string $name
     * @param string $value
     * @return HTMLElement
     */
    public static function inputPassword(string $name, $value = '')
    {
        return HTML::inputText($name, $value)->setAttr('type', 'password');
    }

    /**
     * @param string $name
     * @param string $value
     * @return HTMLElement
     */
    public static function inputNumber(string $name, $value = '')
    {
        return HTML::inputText($name, $value)->setAttr('type', 'number');
    }

    /**
     * @param string $name
     * @param string $value
     * @return HTMLElement
     */
    public static function inputFile(string $name, $value = '')
    {
        return HTML::inputText($name, $value)->setAttr('type', 'file');
    }

    /**
     * @param string $name
     * @param string|integer $value
     * @param boolean $checked
     * @return HTMLElement
     */
    public static function inputCheckbox(string $name, $value = 1, $checked = false)
    {
        $checkboxEl = HTML::inputText($name, $value)->setAttr('type', 'checkbox');

        if ($checked !== false)
            $checkboxEl->setAttr('checked', true);

        return $checkboxEl;
    }

    /**
     * @param string $name
     * @param string|false $label
     * @param false $checked
     * @param false $id
     *
     * @return HTMLElementGroup
     */
    public static function checkboxGroup(string $name, string $label, $checked = false, $id = false)
    {
        $id = ($id === false) ? 'checkbox_combo__' . $name : $id;

        $htmlElList = new HTMLElementGroup();

        if ($label !== false)
            $htmlElList->add(HTML::label($label, $id));

        $htmlElList->add(HTML::inputHidden($name, 0));
        $htmlElList->add(HTML::inputCheckbox($name, 1, $checked)->id($id));

        return $htmlElList;
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
    public static function select(string $name, array $options, $selVal = "", $useTextAsValue = false)
    {
        $el = new HTMLElement('select');

        $el->name($name);

        foreach ($options as $value => $text) {
            $optionEl = new HTMLElement('option');

            $optionEl->setAttr('value', ($useTextAsValue) ? $text : $value);
            $optionEl->innerHTML($text);

            if ($useTextAsValue && $selVal === $text || $useTextAsValue === false && $selVal === $value)
                $optionEl->setAttr('selected', true);

            $el->addElement($optionEl);
        }

        return $el;
    }

    /**
     * Accepts $options as collection
     *
     * @param string $name
     * @param array $options
     * @param string $valField
     * @param string $textField
     * @param string $selVal
     *
     * @return HTMLElement
     */
    public static function selectCollection(string $name, array $options, string $valField, string $textField, $selVal = '')
    {
        $customOptions = [];

        foreach ($options as $key => $val) {
            $val = (array)$val;
            $customOptions[$val[$valField]] = $val[$textField];
        }

        return static::select($name, $customOptions, $selVal);
    }

    /**
     * @param string $name
     * @param string $text
     *
     * @return HTMLElement
     */
    public static function textarea(string $name, string $text)
    {
        $el = new HTMLElement('textarea');

        $el->name($name)->innerText($text);

        return $el;
    }

    public static function form(string $action, $getMethod = false)
    {
        $el = new HTMLElement('form');

        $el->setAttr('action', $action);
        $el->setAttr('method', ($getMethod) ? 'get' : 'post');

        return $el;
    }

    public static function formUpload($action)
    {
        return HTML::form($action)->setAttr('enctype', 'multipart/form-data');
    }

}