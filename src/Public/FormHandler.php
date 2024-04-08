<?php

namespace AbacusPlus\AiNicknameGenerator\Public;

use AbacusPlus\AiNicknameGenerator\Admin\Cache;
use AbacusPlus\AiNicknameGenerator\Admin\Settings;
use ConsoleTVs\Profanity\Builder;
use Error;

class FormHandler
{
    private $data;
    private $generator;
    private $formAttributes;
    private $perPage = 20;
    private $maxPages = 3;

    public function __construct($data, Settings $settings)
    {
        $this->data = $data;

        $this->generator = new Generator($settings->chatGptSettings);

        $this->perPage = $settings->chatGptSettings->numberOfNicknames;

        $this->formAttributes = $settings->formAttributes;
    }

    public function handleFormSubmission()
    {
        $formId = intval($this->data['formId'] ?? 0);

        $page = intval($this->data['page'] ?? 1);

        if ($page) {
            $page--;
        }

        if (!$formId) {
            $this->sendError(__('Missing form id', 'ai_nickname_generator'), 0);
        }

        $post = get_post($formId);

        if (!$post) {
            $this->sendError(__('Invalid form'), 1);
        }

        $data = false;
        $errors = [];
        $cacheKey = null;

        $onlyPresets = get_field('turn_off_ai', $post->ID);

        if (!$onlyPresets) {
            try {
                $formData = new FormData($post->ID, $this->formAttributes);

                $params = [];

                $params = $formData->generatorType ? [
                    ['key' => 'Type', 'value' => $formData->generatorType, 'label' => 'Generator type']
                ] : [];

                foreach ($formData->fields as $field) {
                    $value = trim(isset($this->data[$field->name]) ? $this->data[$field->name] : '');

                    if ($field->required && !$value) {
                        $errors[] = [
                            'name' => $field->name,
                            'error' => sprintf(
                                __('Field is required.', 'ai_nickname_generator'),
                                $field->label
                            )
                        ];
                    } else if (!Builder::blocker($value)->clean()) {
                        $errors[] = [
                            'name' => $field->name,
                            'error' => sprintf(
                                __('Field contains profanity.', 'ai_nickname_generator'),
                                $field->label
                            )
                        ];
                    } else if ($value) {
                        $key = $field->prompt ?: $field->label;

                        if (!$key) {
                            continue;
                        }

                        $label = $field->label;

                        $params[] = compact('key', 'value', 'label');
                    }
                }

                if (!empty($errors) || empty($params)) {
                    $data = false;
                } else {
                    $this->generator->preparePrompt($params);

                    $cacheKey = $this->generator->getPromptKey();
                    $cache = Cache::get($post->ID, $cacheKey);
                    $cacheData = $cache['data'] ?? [];

                    $shouldLoadMore = count($cacheData) < $this->perPage
                        || ($page > 0 && $page < 3 && count($cacheData) < $this->perPage * ($page + 1));

                    $duration = $cache['duration'] ?? 0;

                    if ($shouldLoadMore) {
                        $response = $this->generator->generate(
                            $cacheData,
                            !empty($_SERVER['REMOTE_ADDR']) ? md5($_SERVER['REMOTE_ADDR']) : ''
                        );

                        $data = $response['data'] ?? null;
                        $duration = $response['duration'] ?? null;
                    } else {
                        $data = $cache['data'];
                    }

                    if (is_array($data)) {
                        Cache::set($post->ID, $cacheKey, [
                            'data' => $data,
                            'params' => $params,
                            'createdAt' => $cache['createdAt'] ?? time(),
                            'lastUpdatedAt' => time(),
                            'hits' => ($cache['hits'] ?? 0) + 1,
                            'duration' => $duration,
                        ]);
                    } else {
                        $data = [];
                    }
                }
            } catch (Error $e) {
                $data = false;
            }

            if (empty($data)) {
                $this->sendError('Something went wrong.', 2, $errors);
            }
        } else {
            $cacheKey = 'nickname_hash_' . md5($post->ID);
            $data = [];
        }

        $presetsType = get_field('presets_type', $post->ID) ?: 'repeater';

        $presets = [];

        if ($presetsType === 'repeater') {
            $presets = array_map(fn ($v) => $v['nickname'] ?? '', get_field('presets', $post->ID) ?: []);
        } else if ($presetsType === 'textarea') {
            $presets = explode(PHP_EOL, get_field('presets_textarea', $post->ID) ?: '');
        }


        $data = array_merge(array_values(array_unique(array_filter($presets))), $data);
        $data = array_slice($data, $this->perPage * $page, $this->perPage);

        shuffle($data);

        $this->sendSuccess(
            __('Data retrieved', 'ai_nickname_generator'),
            $this->generateHtml($post->ID, $data, $cacheKey, !$onlyPresets)
        );
    }

    private function generateHtml(int $postId, $items = [], $hash, $ai = true)
    {
        if (!$postId || !is_array($items) || empty($items) || empty($hash)) {
            return '';
        }

        $perPage = $this->perPage;

        ob_start();

        include __DIR__ . '/partials/results.php';

        return ob_get_clean();
    }

    private function sendSuccess($message,  $data, $code = 200)
    {
        $success = true;

        wp_send_json(compact('message', 'code', 'data', 'success'));

        die;
    }

    private function sendError($message, $code, $errors = [])
    {
        $success = false;

        wp_send_json_error(compact('message', 'code', 'success', 'errors'));

        die;
    }
}
