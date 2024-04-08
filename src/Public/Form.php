<?php

namespace AbacusPlus\AiNicknameGenerator\Public;

use AbacusPlus\AiNicknameGenerator\Admin\FormAttributes;

class Form
{
    public $post;
    public $formData;

    public function __construct(int $postId, FormAttributes $formAttributes)
    {
        $this->post = get_post($postId);
        $this->formData = new FormData($postId, $formAttributes);
    }

    public function output()
    {
        if (!$this->post) {
            return;
        }

        ob_start();

        include __DIR__ . '/partials/form.php';

        return ob_get_clean();
    }
}
