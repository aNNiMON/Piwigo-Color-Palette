<?php
defined('COLOR_PALETTE_PATH') or die('Hacking attempt!');

function color_palette_loc_end_section_init()
{
  global $tokens, $page, $conf;

  if ($tokens[0] == 'palette' && (count($tokens) > 1))
  {
    $colorTokens = explode(',', $tokens[1]);
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
        $colorR = (int) ($colorTokens[0]);
        $colorG = (int) ($colorTokens[1]);
        $colorB = (int) ($colorTokens[2]);
        break;
      case 4: // R,G,B,diff
      default:
        $colorR = (int) ($colorTokens[0]);
        $colorG = (int) ($colorTokens[1]);
        $colorB = (int) ($colorTokens[2]);
        $diff = (int) ($colorTokens[3]);
        break;
    }
    $page['section'] = 'palette/' . $tokens[1];

    $searchColorHtml = '<span style="display:inline-block;background-color: rgb('.$colorR.','.$colorG.','.$colorB.');width:8px;height:8px;margin:0 5px;"></span>';
    $page['section_title'] = '<a href="'.get_absolute_root_url().'">'.l10n('Home').'</a>'.$conf['level_separator'].'<a href="'.COLOR_PALETTE_PUBLIC.'">'.l10n('Palette Search').'</a>' . $searchColorHtml;
    $page['title'] = l10n('Palette Search');

    $query = '
SELECT image_id
  FROM '. COLOR_PALETTE_TABLE .'
  WHERE
    (color_r BETWEEN ' . ($colorR - $diff) . ' AND ' . ($colorR + $diff) . ')
    AND
    (color_g BETWEEN ' . ($colorG - $diff) . ' AND ' . ($colorG + $diff) . ')
    AND
    (color_b BETWEEN ' . ($colorB - $diff) . ' AND ' . ($colorB + $diff) . ')
;';
    $page['items'] = array_from_query($query, 'image_id');
  }
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
    $query = '
SELECT `path`
  FROM '. IMAGES_TABLE .'
  WHERE id = ' . $imageId . '
;';
    list($imagePath) = query2array($query, null, 'path');
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
    $params = $color['r'] . ',' . $color['g'] . ',' . $color['b'];
    $palette_colors[] = array(
      'color' => $color['hex'],
      'url'   => make_index_url(array('section' => 'palette')) . '/' . $params
      );
  }
  $template->assign('palette_colors', $palette_colors);
  $template->set_prefilter('picture', 'color_palette_picture_prefilter');
}

function color_palette_picture_prefilter($content)
{
  $search = '{if $display_info.author and isset($INFO_AUTHOR)}';
  $replace = '
<div id="color_palette" class="imageInfo">
  <dt>{\'Palette\'|@translate}</dt>
  <style>.color_palette_item {
  display: block;
  height: 18px;
  width: 22px;
  float: left;
  }</style>
  <dd>
  {foreach from=$palette_colors item=color name=color_loop}<a href="{$color.url}"><span class="color_palette_item" style=" background-color: #{$color.color};" title="#{$color.color}"></span></a>{/foreach}
  <span style="clear: both"/>
  </dd>
</div>
<br/>
';
  return str_replace($search, $replace.$search, $content);
}
