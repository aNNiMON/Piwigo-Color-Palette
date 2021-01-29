<?php
defined('COLOR_PALETTE_PATH') or die('Hacking attempt!');

function color_palette_loc_end_section_init()
{
  global $tokens, $page, $conf, $user;

  if ($tokens[0] != 'palette' || (count($tokens) <= 1))
  {
    return;
  }

  $colorPredicates = array();
  $searchColorHtml = '';
  foreach (explode('x', $tokens[1]) as $colorParameter)
  {
    $colorTokens = explode(',', $colorParameter);
    $colorR = 0;
    $colorG = 0;
    $colorB = 0;
    $diff = 0;
    switch (count($colorTokens))
    {
      case 0:
        break;
      case 1: // GRAY
        $colorR = $colorG = $colorB = (int) ($colorTokens[0]);
        break;
      case 2: // GRAY,diff
        $colorR = $colorG = $colorB = (int) ($colorTokens[0]);
        $diff = (int) ($colorTokens[1]);
        break;
      case 3: // R,G,B
      default:
        $colorR = (int) ($colorTokens[0]);
        $colorG = (int) ($colorTokens[1]);
        $colorB = (int) ($colorTokens[2]);
        break;
      case 4: // R,G,B,diff
        $colorR = (int) ($colorTokens[0]);
        $colorG = (int) ($colorTokens[1]);
        $colorB = (int) ($colorTokens[2]);
        $diff = (int) ($colorTokens[3]);
        break;
    }
    $colorPredicates[] = '(' .
        '(color_r BETWEEN ' . ($colorR - $diff) . ' AND ' . ($colorR + $diff) . ')' .
        ' AND ' .
        '(color_g BETWEEN ' . ($colorG - $diff) . ' AND ' . ($colorG + $diff) . ')' .
        ' AND ' .
        '(color_b BETWEEN ' . ($colorB - $diff) . ' AND ' . ($colorB + $diff) . ')' .
      ')';
    $searchColorHtml .= '<span style="display:inline-block;background-color: rgb('.$colorR.','.$colorG.','.$colorB.');width:8px;height:8px;margin:0 5px;"></span>';
  }
  $page['section'] = 'palette/' . $tokens[1];
  $page['section_title'] = '<a href="'.get_absolute_root_url().'">'.l10n('Home').'</a>'.$conf['level_separator'].'<a href="'.COLOR_PALETTE_PUBLIC.'">'.l10n('Palette Search').'</a>' . $searchColorHtml;
  $page['title'] = l10n('Palette Search');

  $forbidden_categories = calculate_permissions($user['id'], $user['status']);

  $query = '
SELECT 1 as i, pal.image_id as pal_image_id
  FROM '. COLOR_PALETTE_TABLE .' pal
  INNER JOIN '. IMAGES_TABLE .' img ON img.id = pal.image_id
  INNER JOIN '. IMAGE_CATEGORY_TABLE .' cat ON img.id = cat.image_id
  WHERE '. (implode(' OR ', $colorPredicates)) .'
    AND cat.category_id NOT IN ('. $forbidden_categories .')
    AND img.level <= '. intval($user['level']) .'
  GROUP BY pal.image_id
  HAVING SUM(i) = '. count($colorPredicates) .'
;';
  $page['items'] = query2array($query, null, 'pal_image_id');
}

/**
 * Add a prefilter on photo page
 */
function color_palette_loc_end_picture()
{
  global $template, $conf;

  $curr = $template->get_template_vars('current');
  if (!array_key_exists('selected_derivative', $curr))
  {
    return;
  }
  $der = $curr['selected_derivative'];
  $imageId = $der->src_image->id;
  $colors = array();

  $query = '
SELECT COUNT(id) AS nb_colors
  FROM '. COLOR_PALETTE_TABLE .'
  WHERE image_id = ' . $imageId . '
;';
  list($nb_colors) = query2array($query, null, 'nb_colors');
  $nb_colors = (int)$nb_colors;
  if ($nb_colors != $conf['ColorPalette']['colors'])
  {
    // generate new palette
    if (!$conf['ColorPalette']['generate_on_image_page'])
    {
      return;
    }
    $query = '
SELECT `path`
  FROM '. IMAGES_TABLE .'
  WHERE id = ' . $imageId . '
;';
    list($imagePath) = query2array($query, null, 'path');
    // check image validity by extension
    if (!color_palette_is_image($imagePath))
    {
      // create palette from thumbnail
      $imagePath = $der->src_image->rel_path;
      if (!color_palette_is_image($imagePath))
      {
        return;
      }
    }

    list($status, $cols) = generate_palette($imageId, $imagePath);
    if (!$status)
    {
      return;
    }
    $colors = $cols;
  } else {
    // get from DB
    $query = '
SELECT color_r, color_g, color_b
  FROM '. COLOR_PALETTE_TABLE .'
  WHERE image_id = ' . $imageId . '
  ORDER BY `id`
;';
    $result = pwg_query($query);
    while ($row = pwg_db_fetch_assoc($result))
    {
      $r = (int) $row['color_r'];
      $g = (int) $row['color_g'];
      $b = (int) $row['color_b'];
      $colors[] = array(
        'hex' => str_pad(dechex(($r << 16) | ($g << 8) | $b), 6, '0', STR_PAD_LEFT),
        'r' => $r,
        'g' => $g,
        'b' => $b
        );
    }
  }

  $palette_colors = array();
  foreach ($colors as $color)
  {
    $palette_colors[] = array(
      'hex' => $color['hex'],
      'rgb' => $color['r'] . ',' . $color['g'] . ',' . $color['b']
      );
  }
  $template->assign('COLOR_PALETTE_PATH', COLOR_PALETTE_PATH);
  $template->assign('palette_colors', $palette_colors);
  $template->assign('palette_url',  make_index_url(array('section' => 'palette')) . '/');
  $template->set_filename('palette_info_content', realpath(COLOR_PALETTE_PATH . 'template/palette_info.tpl'));
  $template->assign_var_from_handle('INFO_PALETTE', 'palette_info_content');
  $template->set_prefilter('picture', 'color_palette_picture_prefilter');
}

function color_palette_is_image($path)
{
  $pathParts = pathinfo($path);
  $ext = mb_strtolower($pathParts['extension']);
  return in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'));
}

function color_palette_picture_prefilter($content)
{
  $candidates = array(
    // Default layout
    '{if $display_info.author and isset($INFO_AUTHOR)}',
    // Manage Properties Photos
    '<dl id="standard" class="imageInfoTable">{strip}'
  );
  $replace = '{$INFO_PALETTE}';
  foreach ($candidates as $search) {
    if (strpos($content, $search) !== false) {
      return str_replace($search, $replace.$search, $content);
    }
  }
  return $content;
}
