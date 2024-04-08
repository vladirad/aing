<?php

namespace AbacusPlus\AiNicknameGenerator\Admin;

class Cache
{
    public static function set(int $postId, string $key, $value = [])
    {
        if (!empty($value['data'])) {
            $value['data'] = json_encode($value['data']);
        }

        if (!empty($value['params'])) {
            $value['params'] = json_encode($value['params']);
        }

        update_post_meta($postId, $key, $value);
    }

    public static function get(int $postId, string $key)
    {
        if (!$postId) {
            return null;
        }

        $cache = get_post_meta($postId, $key, true);

        if (!empty($cache['data'])) {
            $cache['data'] = json_decode($cache['data']) ?: [];
        }

        if (!empty($cache['params'])) {
            $cache['params'] = json_decode($cache['params']);
        }

        return $cache;
    }
}
