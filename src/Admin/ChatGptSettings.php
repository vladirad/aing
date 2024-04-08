<?php

namespace AbacusPlus\AiNicknameGenerator\Admin;

class ChatGptSettings
{
    public string $authorization = '';
    public string $model = '';
    public int $numberOfNicknames = 60;

    public function __construct($options = [])
    {
        foreach ($options as $key => $value) {
            $key = wp_camel_case($key);

            if (property_exists($this, $key) && $value) {
                $this->$key = gettype($this->$key) === 'integer' ? intval($value) : $value;
            }
        }
    }
}
