<html>
<head>
<title>�������� �����������</title>
</head>
<body>

<h3>���� �������� ���� ������� ���������!</h3>

<ul>
<?php foreach($upload_data as $item => $value):?>
<li><?php echo $item;?>: <?php echo $value;?></li>
<?php endforeach; ?>
</ul>

<p><?php echo anchor('upload', '��������� ��� ����!'); ?></p>

</body>
</html>