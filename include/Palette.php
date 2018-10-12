<?php
defined('COLOR_PALETTE_PATH') or die('Hacking attempt!');

abstract class Palette
{
  /**
   * Palette with weights
   * @const
   */
  private static $PALETTE_WEIGHTS = array(
    0x760000 => 1.00, 0xcc0000 => 1.00, 0xff2222 => 1.05, 0xff7777 => 1.00, 0xffcccc => 1.00,
    0x763b00 => 1.00, 0xcc6600 => 1.00, 0xff9022 => 1.00, 0xffbb77 => 1.05, 0xffe5cc => 1.00,
    0x767600 => 1.00, 0xcbcc00 => 1.00, 0xfeff22 => 1.05, 0xfeff77 => 1.00, 0xffffcc => 1.00,
    0x3b7600 => 1.00, 0x66cc00 => 1.00, 0x90ff22 => 1.00, 0xbbff77 => 1.05, 0xe5ffcc => 1.00,
    0x007600 => 1.00, 0x00cc00 => 1.00, 0x22ff22 => 1.05, 0x77ff77 => 1.00, 0xccffcc => 1.00,
    0x00763b => 1.00, 0x00cc66 => 1.00, 0x22ff90 => 1.00, 0x77ffbb => 1.05, 0xccffe5 => 1.00,
    0x007676 => 1.00, 0x00cbcc => 1.00, 0x22feff => 1.05, 0x77feff => 1.00, 0xccffff => 1.00,
    0x003b76 => 1.00, 0x0065cc => 1.00, 0x2290ff => 1.00, 0x77baff => 1.05, 0xcce5ff => 1.00,
    0x000076 => 1.00, 0x0000cc => 1.00, 0x2222ff => 1.05, 0x7777ff => 1.00, 0xccccff => 1.00,
    0x3b0076 => 1.00, 0x6500cc => 1.00, 0x9022ff => 1.00, 0xba77ff => 1.05, 0xe5ccff => 1.00,
    0x760076 => 1.00, 0xcc00cb => 1.00, 0xff22fe => 1.05, 0xff77fe => 1.00, 0xffccff => 1.00,
    0x76003b => 1.00, 0xcc0066 => 1.00, 0xff2290 => 1.00, 0xff77bb => 1.05, 0xffcce5 => 1.00,
    0x000000 => 1.00, 0x2a2a2a => 0.80, 0x555555 => 0.80, 0x7f7f7f => 0.90, 0xaaaaaa => 0.80, 0xd4d4d4 => 0.80, 0xffffff => 1.00,
    0x151515 => 0.05, 0x3f3f3f => 0.05, 0x6a6a6a => 0.05, 0x959595 => 0.05, 0xbfbfbf => 0.05, 0xeaeaa => 0.05
  );

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
  protected $palette;

  public function __construct($path)
  {
    $this->path = $path;
    $this->initialize($path);
  }

  protected abstract function initialize($path);

  protected abstract function getColor($x, $y);

  public abstract function destroy();

  public function generate($maxColors = 6, $logicalSize = 150, $coverage = 100)
  {
    // Calculate new dimensions based on coverage percentage
    $px = 0;
    $py = 0;
    $pw = $this->width;
    $ph = $this->height;
    if ($coverage < 100)
    {
      $pw = $coverage * $pw / 100;
      $ph = $coverage * $ph / 100;
      $px = $this->width / 2 - $pw / 2;
      $py = $this->height / 2 - $ph / 2;
    }
    // Calculate step
    $picksCount = $logicalSize * $logicalSize;
    $step = sqrt($ph / ($picksCount / $pw));
    $step = max(array(1, $step));
    // Reset number of color occurrences
    $this->palette = array_keys(self::$PALETTE_WEIGHTS);
    $this->palette = array_fill_keys($this->palette, 0);
    // Scan
    for ($y = 0; $y < $ph; $y += $step)
    {
      for ($x = 0; $x < $pw; $x += $step)
      {
        list($r, $g, $b, $a) = $this->getColor((int)($px + $x), (int)($py + $y));
        if ($a === 127)
        {
          continue;
        }
        $color = $this->getClosestColor($r, $g, $b);
        $this->palette[$color] += self::$PALETTE_WEIGHTS[$color];
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