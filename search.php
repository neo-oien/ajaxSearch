<?php 
	// Данные для подключения
	$host='localhost';
	$database='DATABASE';
	$username='USERNAME';
	$password='PASSWORD';
	// Подключение к БД
	$connect = new mysqli($host, $username, $password, $database);
	if ($connect->connect_errno) {printf("Не удалось подключиться: %s\n", $connect->connect_error); exit();}
	$connect->set_charset("utf8");
	// Получаем условия поиска
	if(!empty($_POST['where'])){
		$where = strip_tags($_POST['where']);
		echo "<p>Поиск по фразе: <strong>".$where."</strong></p>\n";
	} else{
		echo "<p class=\"error\">Пустой поисковый запрос</p>\n";
	}
	// Формируем запрос к БД и преобразуем в массив
	$query = "SELECT * FROM `dorohov` WHERE (`title` LIKE '%$where%' OR `content` LIKE '%$where%') ORDER BY `id` ASC";
	$resourses = $connect->query($query);
	$count = $resourses->num_rows;
	while($row = $resourses->fetch_array()){
		$rows[] = $row;
	}
	// Выводим результат
	if ($count > 0) {
		echo "<p>Найдено записей: <strong>".$count."</strong></p>\n<ul>\n";
	} else {
		echo "<p class=\"error\">Записей не найдено, попробуйте изменить запрос.</p>";
	}
	foreach ($rows as $row) {
		echo "<li>id: <strong>".$row['id']."</strong>, Заголовок: <strong>".$row['title']."</strong></li>\n";
	}
	echo "</ul>\n";
	
	//
	$resourses->free();
	$connect->close();
?>