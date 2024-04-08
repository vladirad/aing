<?php

namespace AbacusPlus\AiNicknameGenerator\CPT;

class NicknameForm
{
    const CPT = 'ai_nickname';

    public function init()
    {
        add_action('init', [$this, 'register']);
        add_filter('manage_edit-' . self::CPT . '_columns', [$this, 'addShortcodeColumn']);
        add_filter('manage_' . self::CPT . '_posts_custom_column', [$this, 'shortcodeColumn'], 10, 2);
        add_action('add_meta_boxes', [$this, 'metaboxes']);
    }

    public function register()
    {
        $labels = array(
            'name'                  => _x('Nickname Forms', 'Post Type General Name', 'ai_nickname_generator'),
            'singular_name'         => _x('Nickname Form', 'Post Type Singular Name', 'ai_nickname_generator'),
            'menu_name'             => __('Nickname Forms', 'ai_nickname_generator'),
            'name_admin_bar'        => __('Nickname Form', 'ai_nickname_generator'),
            'archives'              => __('Item Archives', 'ai_nickname_generator'),
            'attributes'            => __('Item Attributes', 'ai_nickname_generator'),
            'parent_item_colon'     => __('Parent Item:', 'ai_nickname_generator'),
            'all_items'             => __('All Forms', 'ai_nickname_generator'),
            'add_new_item'          => __('Add New Item', 'ai_nickname_generator'),
            'add_new'               => __('Add Form', 'ai_nickname_generator'),
            'new_item'              => __('New Item', 'ai_nickname_generator'),
            'edit_item'             => __('Edit Item', 'ai_nickname_generator'),
            'update_item'           => __('Update Item', 'ai_nickname_generator'),
            'view_item'             => __('View Item', 'ai_nickname_generator'),
            'view_items'            => __('View Items', 'ai_nickname_generator'),
            'search_items'          => __('Search Item', 'ai_nickname_generator'),
            'not_found'             => __('Not found', 'ai_nickname_generator'),
            'not_found_in_trash'    => __('Not found in Trash', 'ai_nickname_generator'),
            'featured_image'        => __('Featured Image', 'ai_nickname_generator'),
            'set_featured_image'    => __('Set featured image', 'ai_nickname_generator'),
            'remove_featured_image' => __('Remove featured image', 'ai_nickname_generator'),
            'use_featured_image'    => __('Use as featured image', 'ai_nickname_generator'),
            'insert_into_item'      => __('Insert into item', 'ai_nickname_generator'),
            'uploaded_to_this_item' => __('Uploaded to this item', 'ai_nickname_generator'),
            'items_list'            => __('Items list', 'ai_nickname_generator'),
            'items_list_navigation' => __('Items list navigation', 'ai_nickname_generator'),
            'filter_items_list'     => __('Filter items list', 'ai_nickname_generator'),
        );

        $args = array(
            'label'                 => __('Nickname Form', 'ai_nickname_generator'),
            'description'           => __('Nickname forms', 'ai_nickname_generator'),
            'labels'                => $labels,
            'supports'              => array('title'),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 10,
            'menu_icon'             => 'dashicons-layout',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => false,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capability_type'       => 'page',
            'show_in_rest'          => false,
        );

        register_post_type(self::CPT, $args);
    }

    public function addShortcodeColumn(array $columns)
    {
        $new_columns = array();

        foreach ($columns as $key => $title) {
            $new_columns[$key] = $title;
            if ($key === 'title') {
                $new_columns['ai_nickname_shortcode_column'] = __('Shortcode', 'ai_nickname_generator');
            }
        }

        return $new_columns;
    }

    public function shortcodeColumn(string $column, int $post_id)
    {
        switch ($column) {
            case 'ai_nickname_shortcode_column': {
                    echo sprintf(
                        '<input type="text" value=\'[ainickname id="%s"]\' onClick="this.select();" />',
                        $post_id
                    );
                    break;
                }
        }
    }

    public function metaboxes()
    {
        add_meta_box(
            'ai_nickname_cache_metabox',
            'AI Nickname Cache',
            [$this, 'latestData'],
            'ai_nickname'
        );
    }

    /**
     * @param \WP_Post $post
     */
    public function latestData($post)
    {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT meta_key, meta_value
             FROM {$wpdb->postmeta}
             WHERE post_id = %d
             AND meta_key LIKE 'nickname_hash_%'
             ORDER BY meta_id DESC
             LIMIT 50",
            $post->ID
        );

        $results = $wpdb->get_results($sql);

        if (empty($results)) {
            echo 'No data.';
            return;
        }

        echo '<table class="ai-nicknames-table widefat"><thead><tr><th>Generated at</th><th>Duration</th><th>Params</th><th>Hits</th><th>Data</th></tr></thead><tbody>';

        foreach ($results as $row) {
            $value = maybe_unserialize($row->meta_value);

            if (empty($value['data'])) {
                continue;
            }

            $values = json_decode($value['data'], true) ?: [];
            $params = json_decode($value['params'], true) ?: [];
            $duration = round($value['duration'] ?? 0, 3);

            echo sprintf(
                '<tr><td class="date">%s</td><td class="date">%s</td><td class="params">%s</td><td class="hits">%s</td><td>%s</td></tr>',
                date('Y-m-d H:i:s', $value['createdAt']),
                $duration,
                implode('<br />', array_map(fn ($item) => implode(': ', ['<strong>' . $item['label'] . '</strong>', $item['value']]), $params)),
                $value['hits'] ?? 1,
                implode(", ", $values)
            );
        }

        echo '</tbody></table>';
    }
}
