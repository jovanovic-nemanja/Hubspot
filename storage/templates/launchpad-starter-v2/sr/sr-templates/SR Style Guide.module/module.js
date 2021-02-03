function rgb2hex(rgb) {
  if (rgb.search("rgb") == -1) {
    return rgb;
  } else {
    rgb = rgb.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+))?\)$/);
    function hex(x) {
      return ("0" + parseInt(x).toString(16)).slice(-2);
    }
    return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
  }
}
$(".d-color").each(function () {
  var color = $(this).find("span").css("backgroundColor");
  var hex = rgb2hex(color);
  $(this).find("label").text(hex);
  if (hex == "#ffffff") {
    $(this).find("span").addClass("border");
  }
});