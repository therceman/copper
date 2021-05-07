<?php


namespace Copper\Component\HTML;


use Copper\Handler\StringHandler;

class HTMLCheckboxGroup extends HTMLElementGroup
{
    private $name = null;
    private $checked = false;
    private $id = null;
    private $falseValue = true;

    private $label;

    /** @var HTMLElement */
    private $labelElement;
    /** @var HTMLInput */
    private $inputCheckboxElement;
    /** @var HTMLInput */
    private $inputHiddenElement;

    public function __construct($label)
    {
        $this->label = $label;

        parent::__construct();
    }

    /**
     * @param string $label
     *
     * @return HTMLCheckboxGroup
     */
    public function label(string $label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @param string|null $name
     *
     * @return HTMLCheckboxGroup
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param bool $bool
     *
     * @return HTMLCheckboxGroup
     */
    public function checked(bool $bool)
    {
        $this->checked = $bool;

        return $this;
    }

    /**
     * @param bool $bool
     *
     * @return HTMLCheckboxGroup
     */
    public function falseValue(bool $bool = true)
    {
        $this->falseValue = $bool;

        return $this;
    }

    /**
     * @param string|int|null $id
     *
     * @return HTMLCheckboxGroup
     */
    public function id($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param \Closure $closure
     *
     * @return HTMLCheckboxGroup
     */
    public function inputHiddenElement(\Closure $closure)
    {
        $this->inputHiddenElement = $closure($this->inputHiddenElement);

        return $this;
    }

    /**
     * @param \Closure $closure
     *
     * @return HTMLCheckboxGroup
     */
    public function inputCheckboxElement(\Closure $closure)
    {
        $this->inputCheckboxElement = $closure($this->inputCheckboxElement);

        return $this;
    }

    /**
     * @param \Closure $closure
     *
     * @return HTMLCheckboxGroup
     */
    public function labelElement(\Closure $closure)
    {
        $this->labelElement = $closure($this->labelElement);

        return $this;
    }

    /**
     * @return HTMLInput
     */
    public function getInputHiddenElement()
    {
        return $this->inputHiddenElement;
    }

    /**
     * @return HTMLInput
     */
    public function getInputCheckboxElement()
    {
        return $this->inputCheckboxElement;
    }

    /**
     * @return HTMLElement
     */
    public function getLabelElement()
    {
        return $this->labelElement;
    }

    /**
     * @return HTMLCheckboxGroup
     */
    public function build()
    {
        $name = ($this->name === null) ? StringHandler::random(10) : $this->name;

        $id = ($this->id === null) ? 'checkbox_' . $name : $this->id;

        $checked = ($this->checked === false) ? null : true;

        if ($this->inputHiddenElement === null)
            $this->inputHiddenElement = HTML::inputHidden($name, 0);
        else
            $this->inputHiddenElement->name($name);

        if ($this->inputCheckboxElement === null)
            $this->inputCheckboxElement = HTML::inputCheckbox($name, $checked)->value(1)->id($id);
        else
            $this->inputCheckboxElement->id($id)->name($name)->setAttr('checked', $checked);

        if ($this->labelElement === null)
            $this->labelElement = HTML::label($this->label, $id);
        else
            $this->labelElement->innerText($this->label)->setAttr('for', $id);

        $this->clearList();

        if ($this->falseValue)
            $this->add($this->inputHiddenElement);

        $this->add($this->inputCheckboxElement);

        // TODO this is wrong and should be fixed
        if ($this->label !== '')
            $this->add($this->labelElement);

        return $this;
    }
}