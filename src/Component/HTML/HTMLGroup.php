<?php


namespace Copper\Component\HTML;


class HTMLGroup
{

    /**
     * @param string|false $label
     * @param false $checked
     * @param string $name
     * @param false $id
     * @param bool $hiddenInput
     *
     * @return HTMLElementGroup
     */
    public static function checkbox(string $label, $checked = false, string $name = null, $id = null, $hiddenInput = true)
    {
        $id = ($id === false) ? 'checkbox_' . $name : $id;

        $htmlElList = new HTMLElementGroup();

        if ($hiddenInput === true)
            $htmlElList->add(HTML::inputHidden($name, 0));

        $htmlElList->add(HTML::inputCheckbox($name,  $checked)->value(1)->id($id));

        if ($label !== false)
            $htmlElList->add(HTML::label($label, $id));

        return $htmlElList;
    }

}