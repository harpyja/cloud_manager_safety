function writeSwf(movie, width, height) {
  document.write('<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="' + width + '" height="' + height + '" ID="sf" VIEWASTEXT>');
  document.write('  <param name="movie" value="' + movie + '" />');
  document.write('  <param name="quality" value="high" />');
  document.write('  <param name="wmode" value="window" />');
  document.write('  <param name="allowScriptAccess" value="always" />');
  document.write('  <embed src="' + movie + '" quality="high" name="sf" allowScriptAccess="always" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="' + width + '" height="' + height + '" />');
  document.write('</object>');
}
