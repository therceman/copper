<?php

namespace Copper\Component\Templating;

use Copper\Handler\VarHandler;
use Copper\Sanitizer;

/**
 * Class ViewOutput
 * @package Copper\Component\Templating
 */
class ViewOutput
{

    protected $sanitizer;

    public function __construct()
    {
        $this->sanitizer = new Sanitizer();
    }

    /**
     * Escape Javascript and output
     *
     * @param mixed $value
     * @param bool $wrapIfStr
     *
     * @return string
     */
    public function js($value, $wrapIfStr = false)
    {
        if ($value === null || $value === 'null')
            return 'null';

        if ($value === false || $value === 'false')
            return 'false';

        if ($value === true || $value === 'true')
            return 'true';

        if (is_numeric($value))
            return $value;

        if (is_object($value))
            $value = (array)$value;

        if (VarHandler::isArray($value))
            return $this->json($value);

        if (is_string($value) & $wrapIfStr)
            return "'" . $this->sanitizer->js_escape($value) . "'";

        return $this->sanitizer->js_escape($value);
    }

    /**
     * Escape HTML and output
     *
     * @param string $value
     *
     * @return string
     */
    public function text($value)
    {
        return $this->sanitizer->html_escape($value);
    }

    /**
     * JSON output
     *
     * @param array $value
     *
     * @return string
     */
    public function json($value)
    {
        if ($value === null || is_bool($value))
            return 'null';

        if (is_object($value))
            $value = (array)$value;

        return json_encode($value);
    }

    /**
     * Output (without escape)
     *
     * @param string $value
     *
     * @return string
     */
    public function raw($value)
    {
        return $value;
    }

    /**
     * Output array|string formatted
     *
     * @param mixed $value
     *
     * @return string
     */
    public function dump($value)
    {
        return '<pre>' . $this->sanitizer->html_escape(print_r($value, true)) . '</pre>';
    }

}