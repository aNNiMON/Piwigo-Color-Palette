<?php
defined('COLOR_PALETTE_PATH') or die('Hacking attempt!');

abstract class Palette
{
  /**
   * Width of the image
   * @var int
   */
  protected $width;

  /**
   * Height of the image
   * @var int
   */
  protected $height;

  /**
   * Color palette
   * @var array
   */
  protected $palette = array(
    0x760000, 0xcc0000, 0xff2222, 0xff7777, 0xffcccc,
    0x763b00, 0xcc6600, 0xff9022, 0xffbb77, 0xffe5cc,
    0x767600, 0xcbcc00, 0xfeff22, 0xfeff77, 0xffffcc,
    0x3b7600, 0x66cc00, 0x90ff22, 0xbbff77, 0xe5ffcc,
    0x007600, 0x00cc00, 0x22ff22, 0x77ff77, 0xccffcc,
    0x00763b, 0x00cc66, 0x22ff90, 0x77ffbb, 0xccffe5,
    0x007676, 0x00cbcc, 0x22feff, 0x77feff, 0xccffff,
    0x003b76, 0x0065cc, 0x2290ff, 0x77baff, 0xcce5ff,
    0x000076, 0x0000cc, 0x2222ff, 0x7777ff, 0xccccff,
    0x3b0076, 0x6500cc, 0x9022ff, 0xba77ff, 0xe5ccff,
    0x760076, 0xcc00cb, 0xff22fe, 0xff77fe, 0xffccff,
    0x76003b, 0xcc0066, 0xff2290, 0xff77bb, 0xffcce5,
    0x000000, 0x2a2a2a, 0x555555, 0x7f7f7f, 0xaaaaaa, 0xd4d4d4, 0xffffff
    );

  public function __construct($path)
  {
    $this->path = $path;
    $this->initialize($path);
  }

  protected abstract function initialize($path);

  protected abstract function getColor($x, $y);

  public abstract function destroy();

  public function generate($maxColors = 6, $logicalSize = 150)
  {
    // Calculate step
    $picksCount = $logicalSize * $logicalSize;
    $step = sqrt($this->height / ($picksCount / $this->width));
    $step = max(array(1, $step));
    // Reset number of color occurrences
    $this->palette = array_fill_keys($this->palette, 0);
    // Scan
    for ($y = 0; $y < $this->height; $y += $step)
    {
      for ($x = 0; $x < $this->width; $x += $step)
      {
        list($r, $g, $b, $a) = $this->getColor((int)$x, (int)$y);
        if ($a === 127)
        {
          continue;
        }
        $color = $this->getClosestColor($r, $g, $b);
        $this->palette[$color]++;
      }
    }
    arsort($this->palette);
    $palette = array_keys($this->palette);
    return array_slice($palette, 0, $maxColors, true);
  }

  private function getClosestColor($r, $g, $b)
  {
    $minDistance = PHP_INT_MAX;
    $closest = 0;
    foreach ($this->palette as $color => $count)
    {
      $dr = $r - (($color >> 16) & 0xff);
      $dg = $g - (($color >> 8) & 0xff);
      $db = $b - ($color & 0xff);
      $distance = sqrt(($dr * $dr) + ($dg * $dg) + ($db * $db));
      if ($distance < $minDistance)
      {
        $minDistance = $distance;
        $closest = $color;
      }
    }
    return $closest;
  }
}

class PaletteImagick extends Palette
{
  protected $resource;

  protected function initialize($path)
  {
    $this->resource = new Imagick($path);
    $geometry = $this->resource->getImageGeometry();
    $this->width = $geometry['width'];
    $this->height = $geometry['height'];
  }

  protected function getColor($x, $y)
  {
    $rgb = $this->resource->getImagePixelColor($x, $y)->getColor();
    return array($rgb['r'], $rgb['g'], $rgb['b'], $rgb['a']);
  }

  public function destroy() {
    $this->resource->destroy();
  }
}

class PaletteGD extends Palette
{
  protected $resource;

  protected function initialize($path)
  {
    $info = getImageSize($path);
    $this->width = $info[0];
    $this->height = $info[1];
    $type = strtolower(substr($info['mime'], strpos($info['mime'], '/') + 1));
    $func = 'imagecreatefrom' . $type;
    if (!function_exists($func))
    {
      throw new Exception('The file type (' . $type . ' is not supported');
    }
    $this->resource = $func($path);
  }

  protected function getColor($x, $y)
  {
    $rgb = imageColorAt($this->resource, $x, $y);
    $a = ($rgb >> 24) & 0xff;
    $r = ($rgb >> 16) & 0xff;
    $g = ($rgb >> 8) & 0xff;
    $b = $rgb & 0xff;
    return array($r, $g, $b, $a);
  }

  public function destroy() {
    imageDestroy($this->resource);
  }
}