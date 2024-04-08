<?php

namespace AbacusPlus\AiNicknameGenerator\Public;

use AbacusPlus\AiNicknameGenerator\Admin\ChatGptSettings;
use Error;
use OpenAI;
use Throwable;

class Generator
{
    private $client;
    private $maxTokens = 1024;
    private $model = '';
    private $prompt = '';
    public $number = 60;

    public function __construct(ChatGptSettings $settings, int $maxTokens = 1024)
    {
        $client = OpenAI::client($settings->authorization);

        $this->client = $client;
        $this->model = $settings->model;
        $this->maxTokens = $maxTokens;
        $this->number = $settings->numberOfNicknames + 10; // Generate 10 more in case of duplicates etc
    }

    public function preparePrompt(array $params)
    {
        $text = [];

        foreach ($params as $key => $param) {
            if (is_array($param)) {
                $key = $param['key'];
                $param = $param['value'];
            }

            $param = str_replace(['"', "'", ";", "(", ")", ","], '', $param);

            $param = explode(' ', $param);
            $param = array_slice($param, 0, 5);
            $param = array_filter($param, function ($string) {
                return substr($string, 0, 20);
            });

            $param = implode(' ', $param);


            if (substr_count($key, '%s')) {
                $key = str_replace('%s', $param, $key);

                $text[] = "\"$key\"";
            } else {
                $text[] = "\"$key: $param\"";
            }
        }

        $this->prompt = 'Context:(' . implode(', ', $text) . ');
Question: Generate ' . $this->number . ' unique non-existing nicknames based on the context;';
    }

    public function getPromptKey()
    {
        if (!$this->prompt) {
            throw new Error('Invalid prompt.');
        }

        $prompt = mb_strtoupper($this->prompt . $this->model);

        return 'nickname_hash_' . md5($prompt);
    }

    public function generate($include = [], $user = '')
    {
        if (!$this->prompt) {
            throw new Error('Invalid prompt.');
        }

        set_time_limit(120);

        $prompt = $this->prompt;

        $output = '';

        $start = microtime(true);

        switch ($this->model) {
            case "gpt-3.5-turbo-instruct":
                $results = $this->client->completions()->create([
                    'model' => $this->model,
                    'prompt' => $prompt,
                    'max_tokens' => $this->maxTokens,
                    'n' => 1,
                    'user' => $user,
                    'temperature' => 0.1
                ]);

                $output = $results->choices[0]->text;
                break;

            default:
                $results = $this->client->chat()->create([
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'user', 'content' => $prompt
                        ],
                    ],
                    'max_tokens' => $this->maxTokens,
                    'n' => 1,
                    'user' => $user,
                    'temperature' => 0.1
                ]);

                $output = $results->choices[0]->message->content;
                break;
        }

        $end = microtime(true);

        $output = trim($output);

        if (!$output) {
            throw new Error('Failed fetching data.');
        }

        $response = [
            'duration' => $end - $start,
            'data' => null
        ];

        try {
            $array = explode(PHP_EOL, $output);

            // Check 1
            if (count($array) < 5) {
                return $response;
            }

            $array = array_merge($include, $array);
            $array = array_filter(array_map(function ($name) {
                $text = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $name);
                $text = preg_replace('/^\d+\)\s*/', '', $text);
                $text = preg_replace('/^\d+\.\s*/', '', $text);

                return trim($text);
            }, $array));
            $array = array_unique($array);
            $array = array_values($array);

            // Check 2
            if (count($array) < 5) {
                return $response;
            }

            $response['data'] = $array;

            return $response;
        } catch (Throwable $e) {
            return $response;
        }
    }
}
