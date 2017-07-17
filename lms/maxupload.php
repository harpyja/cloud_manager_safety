<html> 
<body> 
<form method="POST" action="/lib2/upload.cgi" ENCTYPE="multipart/form-data"> 
File 1:  
<input type="file" name="FILE1"> 
<input type="hidden" name="uploaddir"  value="/tmp/mnt/cgitmp">
<input type="hidden" name="rej_url"  value="/lms/maxupload.php">
<br> 
<input type="submit" value="upload">
</form> 
</body> 
</html> 
