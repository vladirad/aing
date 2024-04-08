<?php

namespace AbacusPlus\AiNicknameGenerator\Admin;

use AbacusPlus\AiNicknameGenerator\CPT\NicknameForm;

class Settings
{
    public $chatGptSettings;
    public $formAttributes;

    public function __construct()
    {
        add_action('acf/init', [$this, 'addSettingsPage']);

        $settings = get_field('nickname_settings_ai', 'options') ?? [];

        $this->chatGptSettings = new ChatGptSettings($settings);
        $this->formAttributes = new FormAttributes($settings);
    }

    public function addSettingsPage()
    {
        acf_add_options_sub_page(array(
            'page_title' => __('Settings'),
            'menu_title' => __('Settings'),
            'menu_slug' => 'ai_nickname_settings',
            'parent_slug' => 'edit.php?post_type=' . NicknameForm::CPT
        ));
    }
}
