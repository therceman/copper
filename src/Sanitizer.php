<?php

namespace Copper;


class Sanitizer
{
    /**
     * Javascript escape
     *
     * @param string $value
     *
     * @return mixed|string
     */
    public function js_escape($value)
    {
        // escape backslash
        $value = str_replace('\\', '\u005c', $value);
        // escape forward slash
        $value = str_replace('/', '\u002f', $value);
        // escape single quotes
        $value = str_replace('\'', '\u0027', $value);
        // escape double quotes
        $value = str_replace('"', '\u0022', $value);
        // escape ampersand
        $value = str_replace('&', '\u0026', $value);

        return $value;
    }

    /**
     * HTML escape
     *
     * @param string $value
     *
     * @return string
     */
    public function html_escape($value)
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML401);
    }
}