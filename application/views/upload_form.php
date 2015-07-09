<html>
<head>
<title>Загрузка изображений</title>
</head>
<body>

<?php echo $error;?>

<?php echo form_open_multipart('upload/do_upload');?>

<input type="file" name="userfile" size="20" />

<br /><br />

<input type="submit" value="Загрузить" />

</form>

</body>
</html>