<html>
<head>
<title>Загрузка изображений</title>
</head>
<body>

<h3>Ваша картинка была успешно загружена!</h3>

<ul>
<?php foreach($upload_data as $item => $value):?>
<li><?php echo $item;?>: <?php echo $value;?></li>
<?php endforeach; ?>
</ul>

<p><?php echo anchor('upload', 'Загрузить ещё один!'); ?></p>

</body>
</html>