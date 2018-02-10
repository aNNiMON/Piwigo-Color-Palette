<?php
defined('COLOR_PALETTE_PATH') or die('Hacking attempt!');

function generate_palette($imageId, $imagePath) {
  global $conf;

  // generate new palette
  if (!file_exists($imagePath))
  {
    return array(false, array());
  }

  $query = '
DELETE FROM '. COLOR_PALETTE_TABLE .'
  WHERE image_id = ' . $imageId . '
;';
  pwg_query($query);

  if (extension_loaded('imagick')) {
    $paletteGen = new PaletteImagick(realpath($imagePath));
  } else {
    $paletteGen = new PaletteGD($imagePath);
  }
  $palette = $paletteGen->generate($conf['ColorPalette']['colors'], $conf['ColorPalette']['sample_size']);
  $paletteGen->destroy();
  $colors = array();
  $inserts = array();
  foreach ($palette as $color)
  {
    $hex = str_pad(dechex($color), 6, '0', STR_PAD_LEFT);
    $r = ($color >> 16) & 0xff;
    $g = ($color >> 8) & 0xff;
    $b = $color & 0xff;
    $colors[] = array(
      'hex' => $hex,
      'r' => $r,
      'g' => $g,
      'b' => $b
      );
    $inserts[] = array(
      'image_id' => $imageId,
      'color_r' => $r,
      'color_g' => $g,
      'color_b' => $b,
      );
  }
  mass_inserts(
    COLOR_PALETTE_TABLE,
    array_keys($inserts[0]),
    $inserts
    );
  return array(true, $colors);
}