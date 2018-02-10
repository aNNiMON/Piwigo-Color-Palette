<div class="titrePage">
  <h2>Color Palette</h2>
</div>

<form action="{$COLOR_PALETTE_ADMIN}" method="post" class="properties">

<fieldset id="color_palette">
<legend>{'Configuration'|translate}</legend>
<ul>
  <li class="colors" {if not $ColorPalette.colors}style="display:none;"{/if}>
    <label>
      <b>{'Number of colors'|translate}</b>
      <input type="text" name="colors" value="{$ColorPalette.colors}" size="4">
      <br/>{'Number of colors in palette (default %d)'|translate:COLOR_PALETTE_DEFAULT_COLORS}
    </label>
  </li>
  <li class="sample_size" {if not $ColorPalette.sample_size}style="display:none;"{/if}>
    <label>
      <b>{'Sample image size'|translate}</b>
      <input type="text" name="sample_size" value="{$ColorPalette.sample_size}" size="4">
      <br/>{'Sample image size for palette generation (default %d px)'|translate:COLOR_PALETTE_DEFAULT_SAMPLE_SIZE}
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
