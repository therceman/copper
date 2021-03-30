<?php

namespace Copper\Component\Templating;

use Copper\Sanitizer;

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
     * @param string $value
     *
     * @return string
     */
    public function js($value)
    {
        if ($value === null)
            return 'null';

        if ($value === false)
            return 'false';

        if ($value === true)
            return 'true';

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