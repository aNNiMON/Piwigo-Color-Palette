{combine_script id='jquery.ui.slider' require='jquery.ui' load='footer' path='themes/default/js/ui/minified/jquery.ui.slider.min.js'}
{combine_css path="themes/default/js/ui/theme/jquery.ui.slider.css"}

<style>
#colorpalette_colors,
#colorpalette_sample_size {
  width:400px;
  display:inline-block;
  margin-right:10px;
}
#colorpalette_colors_handle,
#colorpalette_sample_size_handle {
  width: 3em;
  height: 1.6em;
  top: 50%;
  margin-top: -.8em;
  text-align: center;
  line-height: 1.6em;
}
</style>

{footer_script}{literal}
$(document).ready(function() {
  var colors_handle = $( "#colorpalette_colors_handle" );
  jQuery("#colorpalette_colors").slider({
    range: "min",
    min: 3,
    max: 16,
    value: {/literal}{$ColorPalette.colors}{literal},
    create: function() {
      colors_handle.text( $( this ).slider( "value" ) );
    },
    slide: function( event, ui ) {
      colors_handle.text( ui.value );
    },
    stop: function( event, ui ) {
      jQuery("input[name=colors]").val(ui.value);
    }
  });
  var sample_size_handle = $( "#colorpalette_sample_size_handle" );
  jQuery("#colorpalette_sample_size").slider({
    range: "min",
    min: 50,
    max: 400,
    step: 50,
    value: {/literal}{$ColorPalette.sample_size}{literal},
    create: function() {
      sample_size_handle.text( $( this ).slider( "value" ) );
    },
    slide: function( event, ui ) {
      sample_size_handle.text( ui.value );
    },
    stop: function( event, ui ) {
      jQuery("input[name=sample_size]").val(ui.value);
    }
  });
});
{/literal}{/footer_script}


<div class="titrePage">
  <h2>Color Palette</h2>
</div>

<form action="{$COLOR_PALETTE_ADMIN}" method="post" class="properties">

<fieldset id="color_palette">
<legend>{'Configuration'|translate}</legend>
<ul>
  <li class="colors" {if not $ColorPalette.colors}style="display:none;"{/if}>
    <label>
      <b>{'Number of colors'|translate}</b><br/>
      <div id="colorpalette_colors">
          <div id="colorpalette_colors_handle" class="ui-slider-handle"></div>
      </div>
      <input type="hidden" name="colors" value="{$ColorPalette.colors}">
      <br/>{'Number of colors in palette (default %d)'|translate:COLOR_PALETTE_DEFAULT_COLORS}
    </label>
  </li>
  <li class="sample_size" {if not $ColorPalette.sample_size}style="display:none;"{/if}>
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
