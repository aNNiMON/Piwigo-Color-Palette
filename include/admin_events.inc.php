<?php
defined('COLOR_PALETTE_PATH') or die('Hacking attempt!');

function color_palette_admin_plugin_menu_links($menu)
{
  $menu[] = array(
    'NAME' => l10n('Color Palette'),
    'URL' => COLOR_PALETTE_ADMIN,
    );
  return $menu;
}

function color_palette_loc_end_element_set_global()
{
  global $template;

  $template->append('element_set_global_plugins_actions', array(
    'ID' => 'generate_palette',
    'NAME' => l10n('Generate color palette')
    ));
  $template->append('element_set_global_plugins_actions', array(
    'ID' => 'clear_palette',
    'NAME' => l10n('Clear color palette')
    ));
}

function color_palette_element_set_global_action($action, $collection)
{
  global $page;

  if ($action == 'clear_palette')
  {
    $query = '
DELETE FROM '.COLOR_PALETTE_TABLE.'
  WHERE image_id IN ('.implode(',', $collection).')
;';
    pwg_query($query);
    $page['infos'][] = l10n('Operation successfully completed');
  }
  if ($action == 'generate_palette')
  {
    $query = '
SELECT `id`, `file`, `path`
  FROM '.IMAGES_TABLE.'
  WHERE id IN ('.implode(',',$collection).')
;';
    $result = pwg_query($query);
    while ($row = pwg_db_fetch_assoc($result))
    {
      $filename = $row['path'];
      if (!is_readable($filename))
      {
        $page['warnings'][] = l10n('Unable to process %s', $row['file']);
        continue;
      }
      list($status, $colors) = generate_palette($row['id'], $filename);
      if (!$status)
      {
        $page['warnings'][] = l10n('Unable to process %s', $row['file']);
        continue;
      }
    }
    $page['infos'][] = l10n('Operation successfully completed');
  }
}

function color_palette_get_batch_manager_prefilters($prefilters)
{
  $prefilters[] = array(
    'ID' => 'color_palette_with_palette',
    'NAME' => l10n('With color palette'),
    );
  $prefilters[] = array(
    'ID' => 'color_palette_without_palette',
    'NAME' => l10n('Without color palette'),
    );

  return $prefilters;
}

function color_palette_perform_batch_manager_prefilters($filter_sets, $prefilter)
{
  switch ($prefilter)
  {
  case 'color_palette_with_palette':
  case 'color_palette_without_palette':
    $criteria = ($prefilter == 'color_palette_without_palette') ? 'IS NULL' : 'IS NOT NULL';
    $query = '
SELECT
    DISTINCT('.IMAGES_TABLE.'.id)
  FROM '.IMAGES_TABLE.'
  LEFT JOIN '.COLOR_PALETTE_TABLE.'
    ON '.IMAGES_TABLE.'.id = '.COLOR_PALETTE_TABLE.'.image_id
  WHERE
    '.COLOR_PALETTE_TABLE.'.image_id '.$criteria.'
;';
    $filter_sets[] = query2array($query, null, 'id');
    break;
  }
  return $filter_sets;
}
