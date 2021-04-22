<?php


namespace Copper\Component\HTML;


use Copper\Handler\StringHandler;

class HTMLRadioGroup extends HTMLElementGroup
{
    private $checked = false;
    private $id = null;
    private $value = null;

    private $label;
    private $name;

    private $labelElement;
    private $inputElement;

    public function __construct($label, $name)
    {
        $this->label = $label;
        $this->name = $name;

        parent::__construct();
    }

    /**
     * @param string $label
     *
     * @return HTMLRadioGroup
     */
    public function label(string $label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return HTMLRadioGroup
     */
    public function name(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param bool $bool
     *
     * @return HTMLRadioGroup
     */
    public function checked(bool $bool)
    {
        $this->checked = $bool;

        return $this;
    }

    /**
     * @param string|int|null $value
     *
     * @return HTMLRadioGroup
     */
    public function value($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @param string|int|null $id
     *
     * @return HTMLRadioGroup
     */
    public function id($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return HTMLInput
     */
    public function getInputElement()
    {
        return $this->inputElement;
    }

    /**
     * @return HTMLElement
     */
    public function getLabelElement()
    {
        return $this->labelElement;
    }

    /**
     * @return HTMLRadioGroup
     */
    public function build()
    {
        $this->name = ($this->name === null) ? StringHandler::random(10) : $this->name;

        $this->id = ($this->id === null) ? 'radio_' . $this->name . '_' . StringHandler::transliterate($this->label) : $this->id;

        $this->labelElement = HTML::label($this->label, $this->id);
        $this->inputElement = HTML::inputRadio($this->name, $this->value, $this->checked)->id($this->id);

        $this->clearList();
        $this->add($this->inputElement);
        $this->add($this->labelElement);

        return $this;
    }
}