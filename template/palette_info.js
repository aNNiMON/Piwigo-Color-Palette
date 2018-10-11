var paletteColors = {};

function paletteItemClick(ins) {
  $(ins).toggleClass('selected');
  var color = ins.getAttribute('data-color');
  if (color in paletteColors) {
    delete paletteColors[color];
  } else {
    paletteColors[color] = true;
  }
  var selectedColors = [];
  $.each(paletteColors, function(k, v) {
    selectedColors.push(k);
  });
  if (selectedColors.length == 0) {
    $('#palette_search').fadeOut();
  } else {
    $('#palette_search').attr('href', paletteUrl + selectedColors.join('x')).fadeIn();
  }
}