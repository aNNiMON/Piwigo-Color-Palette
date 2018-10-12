{combine_script id='jquery.ui.slider' require='jquery.ui' load='footer' path='themes/default/js/ui/minified/jquery.ui.slider.min.js'}
{combine_css path="themes/default/js/ui/theme/jquery.ui.slider.css"}
{combine_css id="colorpalette.admin_css" path=$COLOR_PALETTE_PATH|cat:"template/admin.css"}
{combine_script id="colorpalette.admin_js" require="jquery" load="async" path=$COLOR_PALETTE_PATH|cat:"template/admin.js"}
{footer_script}
  var paletteColors = {(is_null($ColorPalette.colors)) ? $COLOR_PALETTE_DEFAULT_COLORS : $ColorPalette.colors};
  var paletteSampleSize = {(is_null($ColorPalette.sample_size)) ? $COLOR_PALETTE_DEFAULT_SAMPLE_SIZE : $ColorPalette.sample_size};
  var paletteCoverage = {(is_null($ColorPalette.coverage)) ? 100 : $ColorPalette.coverage};
{/footer_script}

<div class="titrePage">
  <h2>Color Palette</h2>
</div>

<form action="{$COLOR_PALETTE_ADMIN}" method="post" class="properties">

<fieldset id="color_palette">
<legend>{'Configuration'|translate}</legend>
<ul>
  <li>
    <label>
      <b>{'Number of colors'|translate}</b><br/>
      <div id="colorpalette_colors">
          <div id="colorpalette_colors_handle" class="ui-slider-handle"></div>
      </div>
      <input type="hidden" name="colors" value="{$ColorPalette.colors}">
      <br/>{'Number of colors in palette (default %d)'|translate:COLOR_PALETTE_DEFAULT_COLORS}
    </label>
  </li>
  <li>
    <label>
      <b>{'Sample image size'|translate}</b><br/>
      <div id="colorpalette_sample_size">
          <div id="colorpalette_sample_size_handle" class="ui-slider-handle"></div>
      </div>
      <input type="hidden" name="sample_size" value="{$ColorPalette.sample_size}">
      <br/>{'Sample image size for palette generation (default %d px)'|translate:COLOR_PALETTE_DEFAULT_SAMPLE_SIZE}
    </label>
  </li>
  <li>
    <label>
      <b>{'Sample image coverage'|translate}</b><br/>
      <div id="colorpalette_coverage">
          <div id="colorpalette_coverage_handle" class="ui-slider-handle"></div>
      </div>
      <input type="hidden" name="coverage" value="{$ColorPalette.coverage}">
<br/>{'Sample image coverage from center (default 100% - full image)'|translate}
    </label>
  </li>
  <li>
    <canvas id="canvas" width="320" height="180"></canvas>
  </li>
  <li>
    <input type="checkbox" id="generate_on_image_page" name="generate_on_image_page"{if $ColorPalette.generate_on_image_page} checked="checked"{/if}>
    <label for="generate_on_image_page">
      <b>{'Generate palette on image page'|translate}</b>
      <br/>{'If palette is not yet generated for the image, this will be done the first time the image is opened'|translate}
    </label>
  </li>
  <li>
    <input type="checkbox" id="clear" name="clear">
    <label for="clear">
      <b>{'Clear all palettes'|translate}</b>
    </label>
  </li>
</ul>
</fieldset>

<p class="formButtons">
  <input type="hidden" name="pwg_token" value="{$PWG_TOKEN}">
  <input type="submit" name="submit" value="{'Save Settings'|translate}">
</p>
</form>
