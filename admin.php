<?php
defined('COLOR_PALETTE_PATH') or die('Hacking attempt!');

global $template, $page, $conf;

include_once(PHPWG_ROOT_PATH.'admin/include/tabsheet.class.php');
$page['tab'] = (isset($_GET['tab'])) ? $_GET['tab'] : $page['tab'] = 'config';

$tabsheet = new tabsheet();
$tabsheet->add('config', l10n('Configuration'), COLOR_PALETTE_ADMIN.'-config');
$tabsheet->select($page['tab']);
$tabsheet->assign();

if (isset($_POST['submit']))
{
  check_pwg_token();
  $old_conf = $conf;
  $colors = isset($_POST['colors']) ? (intval($_POST['colors'])) : $old_conf['colors'];
  if (($colors < 3) || ($colors > 16))
  {
    $page['errors'][] = l10n('Number of colors must have a value between %d and %d', 3, 16);
    $colors = $old_conf['colors'];
  }

  $sample_size = isset($_POST['sample_size']) ? (intval($_POST['sample_size'])) : $old_conf['sample_size'];
  if (($sample_size < 50) || ($sample_size > 400))
  {
    $page['errors'][] = l10n('Sample image must have a size between %d and %d', 50, 400);
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
