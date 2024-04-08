<?php

namespace AbacusPlus\AiNicknameGenerator;

use AbacusPlus\AiNicknameGenerator\Admin\Settings;
use AbacusPlus\AiNicknameGenerator\CPT\NicknameForm;
use AbacusPlus\AiNicknameGenerator\Public\Form;
use AbacusPlus\AiNicknameGenerator\Public\FormHandler;

class NicknameGenerator
{
    private $settings;

    public function __construct()
    {
        if (!class_exists('ACF')) {
            return;
        }

        $this->settings = new Settings();

        $cpt = new NicknameForm();
        $cpt->init();

        add_shortcode('ainickname', [$this, 'registerShortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);

        add_action('wp_ajax_ai_generate_nickname', [$this, 'handleAjaxRequest']);
        add_action('wp_ajax_nopriv_ai_generate_nickname', [$this, 'handleAjaxRequest']);
    }

    public function enqueueScripts()
    {
        wp_enqueue_script('ai_nickname_generator', AI_NICKNAME_PLUGIN_URL . 'assets/scripts.js', ['jquery'], AI_NICKNAME_GENERATOR_VERSION);

        wp_localize_script('ai_nickname_generator', 'aiNickname', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => \wp_create_nonce('generate-nickname-nonce'),
        ]);

        wp_register_style('ai_nickname_generator_css', AI_NICKNAME_PLUGIN_URL . 'assets/styles.css', false, AI_NICKNAME_GENERATOR_VERSION);
        wp_enqueue_style('ai_nickname_generator_css');
    }

    public function adminEnqueueScripts()
    {
        wp_register_style('ai_nickname_generator_admin_css', AI_NICKNAME_PLUGIN_URL . 'assets/admin.css', false, AI_NICKNAME_GENERATOR_VERSION);
        wp_enqueue_style('ai_nickname_generator_admin_css');
    }

    public function registerShortcode($atts)
    {
        $atts = shortcode_atts(array(
            'id' => '',
        ), $atts, 'ainickname');

        $id = intval($atts['id']);


        $form = new Form($id, $this->settings->formAttributes);
        return $form->output();
    }


    public function handleAjaxRequest()
    {
        check_ajax_referer('generate-nickname-nonce', 'security');

        $data = $_POST;

        $formHandler = new FormHandler($data, $this->settings);

        $response = $formHandler->handleFormSubmission();
        wp_send_json_success($response);
    }
}
