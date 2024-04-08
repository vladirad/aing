<?php

/**
 * Plugin Name:       AI Nickname Generator
 * Plugin URI:        https://www.findnicknames.com/nickname-generator/
 * Description:       Generate nicknames using ChatGPT
 * Version:           1.0.8
 * Author:            Vladimir Radisic
 * Author URI:        https://codeable.io/developers/vladimir-radisic/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ai_nickname_generator
 * Domain Path:       /languages
 */


if (!defined('WPINC')) {
	die;
}

function ai_nickname_generator_acf_admin_notice()
{
	if (get_transient('ai_nickname_generator_acf_not_found')) {
		echo '<div class="notice notice-warning is-dismissible"><p>';
		echo __('AI Nickname Generator requires Advanced Custom Fields PRO to be installed and activated.', 'ai_nickname_generator');
		echo '</p></div>';

		delete_transient('ai_nickname_generator_acf_not_found');
	}
}

function ai_nickname_generator_check_acf_pro()
{
	if (!class_exists('ACF')) {
		if (is_plugin_active(plugin_basename(__FILE__))) {
			deactivate_plugins(plugin_basename(__FILE__));

			add_action('admin_notices', 'ai_nickname_generator_acf_admin_notice');
			set_transient('ai_nickname_generator_acf_not_found', true, 5);
		}
	}
}

add_action('admin_init', 'ai_nickname_generator_check_acf_pro');

define('AI_NICKNAME_GENERATOR_VERSION', '1.0.0');
define('AI_NICKNAME_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AI_NICKNAME_PLUGIN_PATH', plugin_dir_path(__FILE__));

require_once("vendor/autoload.php");

if (!function_exists('wp_camel_case')) {
	function wp_camel_case($input, $separator = '_')
	{
		return lcfirst(str_replace($separator, '', ucwords($input, $separator)));
	}
}

use AbacusPlus\AiNicknameGenerator\Admin\Cache;
use AbacusPlus\AiNicknameGenerator\NicknameGenerator;

add_action('plugins_loaded', function () {
	new NicknameGenerator();
});

function my_acf_json_load_point($paths)
{
	$paths[] = AI_NICKNAME_PLUGIN_PATH . '/acf-json';

	return $paths;
}

add_filter('acf/settings/load_json', 'my_acf_json_load_point');

function nickname_generator_cache()
{
	return new Cache();
}
