<?php

namespace AbacusPlus\AiNicknameGenerator\Public;

class FormDataField
{
    public string $name = '';
    public string $type = '';
    public string $label = '';
    public string $placeholder = '';
    public $classNames = [];
    public string $prompt = '';
    public $options = [];
    public bool $required;

    public function __construct($data = [])
    {
        if (!empty($data['label'])) {
            $this->label = $data['label'];
        }

        if (!empty($data['input_type'])) {
            $this->type = $data['input_type'];
        }

        if (!empty($data['placeholder'])) {
            $this->placeholder = $data['placeholder'];
        }

        if (!empty($data['chatgpt_prompt'])) {
            $this->prompt = $data['chatgpt_prompt'];
        }

        if ($this->type && $this->type === 'select') {
            $this->classNames = $data['html_elements_select'] ?? [];
            $this->options = $data['options'] ?? [];
        } else {
            $this->classNames = $data['html_elements_input'] ?? [];
        }

        $this->required = $data['required'] ?? false;

        $this->name = sanitize_key(trim($this->label ?: $this->placeholder));
    }
}
