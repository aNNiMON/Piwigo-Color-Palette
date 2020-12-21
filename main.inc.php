<?php
/*
Plugin Name: Color Palette
Version: 1.0.7
Description: Extracts color palette from images and adds an ability to search by color.
Plugin URI: auto
Author: aNNiMON
Author URI: https://annimon.com/
Has Settings: true
*/

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

/*
 * Plugin constants
 */
global $prefixeTable;
define('COLOR_PALETTE_ID',      basename(dirname(__FILE__)));
define('COLOR_PALETTE_PATH' ,   PHPWG_PLUGINS_PATH . COLOR_PALETTE_ID . '/');
define('COLOR_PALETTE_TABLE',   $prefixeTable . 'color_palette');
define('COLOR_PALETTE_ADMIN',   get_root_url() . 'admin.php?page=plugin-' . COLOR_PALETTE_ID);
define('COLOR_PALETTE_PUBLIC',  get_absolute_root_url() . make_index_url(array('section' => 'palette')) . '/');
define('COLOR_PALETTE_DIR',     PHPWG_ROOT_PATH . PWG_LOCAL_DIR . COLOR_PALETTE_ID . '/');
define('COLOR_PALETTE_VERSION', '1.0.7');
// Settings defaults
define('COLOR_PALETTE_DEFAULT_COLORS', '8');
define('COLOR_PALETTE_DEFAULT_SAMPLE_SIZE', '150');


/*
 * Event handlers
 */
add_event_handler('init', 'color_palette_init');

if (defined('IN_ADMIN'))
{
  $admin_file = COLOR_PALETTE_PATH . 'include/admin_events.inc.php';
  add_event_handler('get_admin_plugin_menu_links', 'color_palette_admin_plugin_menu_links',
      EVENT_HANDLER_PRIORITY_NEUTRAL, $admin_file);
  add_event_handler('loc_end_element_set_global', 'color_palette_loc_end_element_set_global',
      EVENT_HANDLER_PRIORITY_NEUTRAL, $admin_file);
  add_event_handler('element_set_global_action', 'color_palette_element_set_global_action',
      EVENT_HANDLER_PRIORITY_NEUTRAL, $admin_file);
  // Prefilter hooks
  add_event_handler('get_batch_manager_prefilters', 'color_palette_get_batch_manager_prefilters',
      EVENT_HANDLER_PRIORITY_NEUTRAL, $admin_file);
  add_event_handler('perform_batch_manager_prefilters', 'color_palette_perform_batch_manager_prefilters',
      EVENT_HANDLER_PRIORITY_NEUTRAL, $admin_file);
}
else
{
  $public_file = COLOR_PALETTE_PATH . 'include/public_events.inc.php';
  add_event_handler('loc_end_section_init', 'color_palette_loc_end_section_init',
      EVENT_HANDLER_PRIORITY_NEUTRAL, $public_file);
  add_event_handler('loc_end_picture', 'color_palette_loc_end_picture',
      EVENT_HANDLER_PRIORITY_NEUTRAL, $public_file);
}


function color_palette_init()
{
  global $conf;

  load_language('plugin.lang', COLOR_PALETTE_PATH);

  $conf['ColorPalette'] = safe_unserialize($conf['ColorPalette']);

  require(COLOR_PALETTE_PATH . 'include/Palette.php');
  require(COLOR_PALETTE_PATH . 'include/functions.inc.php');
}
