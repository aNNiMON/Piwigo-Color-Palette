$(document).ready(function() {
  var ctx = canvas.getContext('2d');
  var w = canvas.width, h = canvas.height;

  var colors_handle = $( '#colorpalette_colors_handle' );
  jQuery('#colorpalette_colors').slider({
    range: 'min',
    min: 3,
    max: 16,
    value: paletteColors,
    create: function() {
      colors_handle.text( $( this ).slider( 'value' ) );
    },
    slide: function( event, ui ) {
      colors_handle.text( ui.value );
    },
    stop: function( event, ui ) {
      jQuery('input[name=colors]').val(ui.value);
    }
  });

  var sample_size_handle = $( '#colorpalette_sample_size_handle' );
  jQuery('#colorpalette_sample_size').slider({
    range: 'min',
    min: 50,
    max: 400,
    step: 50,
    value: paletteSampleSize,
    create: function() {
      sample_size_handle.text( $( this ).slider( 'value' ) );
    },
    slide: function( event, ui ) {
      sample_size_handle.text( ui.value );
      paletteSampleSize = ui.value;
      redraw();
    },
    stop: function( event, ui ) {
      jQuery('input[name=sample_size]').val(ui.value);
    }
  });

  var sample_coverage_handle = $( '#colorpalette_coverage_handle' );
  jQuery('#colorpalette_coverage').slider({
    range: 'min',
    min: 30,
    max: 100,
    step: 10,
    value: paletteCoverage,
    create: function() {
      sample_coverage_handle.text( $( this ).slider( 'value' ) + '%' );
    },
    slide: function( event, ui ) {
      sample_coverage_handle.text( ui.value + '%' );
      paletteCoverage = ui.value;
      redraw();
    },
    stop: function( event, ui ) {
      jQuery('input[name=coverage]').val(ui.value);
    }
  });
  
  function redraw() {
    var lw = 2;
    var pw = paletteCoverage * w / 100;
    var ph = paletteCoverage * h / 100;
    var px = w / 2 - pw / 2 + lw;
    var py = h / 2 - ph / 2 + lw;
    pw -= lw * 2;
    ph -= lw * 2;
    ctx.fillStyle = '#F1B49E';
    ctx.fillRect(0, 0, w, h);
    ctx.fillStyle = '#E26A3D';
    ctx.strokeStyle = '#000';
    ctx.lineWidth = lw;
    ctx.beginPath();
    ctx.rect(px, py, pw, ph);
    ctx.stroke();
    ctx.fill();
    ctx.closePath();

    var picksCount = 4 * paletteSampleSize;
    var step = Math.max(1, Math.sqrt(ph / (picksCount / pw)));
    ctx.fillStyle = '#fff';
    for (var y = 0; y < ph; y += step) {
      for (var x = 0; x < pw; x += step) {
        ctx.fillRect(px + x, py + y, 1, 1);
      }
    }
  }
  
  redraw();
});