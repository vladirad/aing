<?php

namespace AbacusPlus\AiNicknameGenerator\Public;

use AbacusPlus\AiNicknameGenerator\Admin\FormAttributes;

class FormData
{
    public $formAttributes;
    /** @var FormDataField[] */
    public $fields = [];
    public $generatorType = '';

    public function __construct(int $postId, FormAttributes $formAttributes)
    {
        $formData = get_fields($postId);

        if (!empty($formData['form_items']) && is_array($formData['form_items'])) {
            foreach ($formData['form_items'] as $field) {
                $this->fields[] = new FormDataField($field);
            }
        }

        if (!empty($formData['generator_type'])) {
            $this->generatorType = $formData['generator_type'];
        }

        if (!empty($formData['show_title'])) {
            $this->showTitle = $formData['show_title'];
        }

        $this->formAttributes = $formAttributes->merge($formData['html_elements'] ?? []);
    }
}
