<?php

namespace AbacusPlus\AiNicknameGenerator\Admin;

class FormAttributes
{
    public string $wrapperClass = '';
    public string $inputWrapperClass = '';
    public string $inputClass = '';
    public string $selectWrapperClass = '';
    public string $selectClass = '';
    public string $buttonClass = '';
    public string $labelClass = '';

    public function __construct($options = [])
    {
        foreach ($options as $key => $value) {
            $key = wp_camel_case($key);

            if (property_exists($this, $key) && $value) {
                $this->$key = $value;
            }
        }
    }

    public function merge($options = []): self
    {
        $newInstance = new self();

        foreach ($this as $property => $value) {
            $newInstance->$property = $value;
        }

        foreach ($options as $key => $value) {
            $key = wp_camel_case($key);

            if (property_exists($newInstance, $key) && $value) {
                $newInstance->$key = $value;
            }
        }

        return $newInstance;
    }
}
