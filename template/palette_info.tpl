{strip}
{combine_css id="colorpalette.paletteinfo_css" path=$COLOR_PALETTE_PATH|cat:"template/palette_info.css"}
{combine_script id="colorpalette.paletteinfo_js" require="jquery" load="async" path=$COLOR_PALETTE_PATH|cat:"template/palette_info.js"}
{footer_script}
  var paletteUrl = '{$palette_url}';
{/footer_script}

<div id="color_palette" class="imageInfo">
  <dt>{'Palette'|@translate}</dt>
  <dd id="palette_colors">
    {foreach
      from=$palette_colors item=color name=color_loop}
        <div class="color_palette_item"
             style="background-color: #{$color.hex};"
             data-color="{$color.rgb}"
             onclick="paletteItemClick(this);"
             title="#{$color.hex}"></div>
    {/foreach}
    &nbsp;<a id="palette_search" href="#" style="display: none;">{'Search'|@translate}</a>
    <div style="clear: both"/>
  </dd>
</div>
{/strip}