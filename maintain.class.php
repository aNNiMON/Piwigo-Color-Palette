<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

class ColorPalette_maintain extends PluginMaintain
{
  private $default_conf = array(
    'colors' => 8,
    'sample_size' => 150,
    'coverage' => 100,
    'generate_on_image_page' => true
    );

  private $table;

  function __construct($plugin_id)
  {
    parent::__construct($plugin_id);

    global $prefixeTable;
    $this->table = $prefixeTable . 'color_palette';
  }

  function install($plugin_version, &$errors=array())
  {
    global $conf;

    if (empty($conf['ColorPalette']))
    {
      conf_update_param('ColorPalette', $this->default_conf, true);
    }
    else
    {
      $old_conf = safe_unserialize($conf['ColorPalette']);

      // Add missing parameters to conf
      foreach ($this->default_conf as $key => $value)
      {
        if (!array_key_exists($key, $old_conf))
        {
          $old_conf[$key] = $value;
        }
      }

      conf_update_param('ColorPalette', $old_conf, true);
    }

    $query = '
CREATE TABLE IF NOT EXISTS `' . $this->table . '` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `color_r` tinyint(3) unsigned NOT NULL,
  `color_g` tinyint(3) unsigned NOT NULL,
  `color_b` tinyint(3) unsigned NOT NULL,
  `image_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `image_id` (`image_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
;';
    pwg_query($query);
  }

  function update($old_version, $new_version, &$errors=array())
  {
    $this->install($new_version, $errors);
  }

  function uninstall()
  {
    conf_delete_param('ColorPalette');

    pwg_query('DROP TABLE `' . $this->table . '`;');
  }
}