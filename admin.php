<?php
defined('COLOR_PALETTE_PATH') or die('Hacking attempt!');

global $template, $page, $conf;

if (isset($_POST['submit']))
{
  check_pwg_token();
  $old_conf = $conf;
  $colors = isset($_POST['colors']) ? (intval($_POST['colors'])) : $old_conf['colors'];
  if (($colors < 1) || ($colors > 30))
  {
    $page['errors'][] = l10n('Number of colors must have a value between %d and %d', 1, 30);
    $colors = $old_conf['colors'];
  }

  $sample_size = isset($_POST['sample_size']) ? (intval($_POST['sample_size'])) : $old_conf['sample_size'];
  if (($sample_size < 5) || ($sample_size > 400))
  {
    $page['errors'][] = l10n('Sample image must have a size between %d and %d', 5, 400);
    $sample_size = $old_conf['sample_size'];
  }

  if (empty($page['errors']))
  {
    $conf['ColorPalette'] = array(
      'colors' => $colors,
      'sample_size' => $sample_size,
      'generate_on_image_page' => isset($_POST['generate_on_image_page'])
      );
    if (isset($_POST['clear']))
    {
      pwg_query('TRUNCATE `'. COLOR_PALETTE_TABLE .'`;');
    }
    conf_update_param('ColorPalette', serialize($conf['ColorPalette']));
    $page['infos'][] = l10n('Configuration successfully updated');
  }
}

$template->assign(array(
  'COLOR_PALETTE_ADMIN' => COLOR_PALETTE_ADMIN,
  'COLOR_PALETTE_DEFAULT_COLORS' => COLOR_PALETTE_DEFAULT_COLORS,
  'COLOR_PALETTE_DEFAULT_SAMPLE_SIZE' => COLOR_PALETTE_DEFAULT_SAMPLE_SIZE,
  'ColorPalette' => $conf['ColorPalette'],
  'PWG_TOKEN' => get_pwg_token()
  ));

$template->set_filename('plugin_admin_content', realpath(COLOR_PALETTE_PATH . 'template/admin.tpl'));
$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');
