<?php 
	// Данные для подключения
	$host='localhost';
	$database='DATABASE';
	$username='USERNAME';
	$password='PASSWORD';
	$table='TABLE';
	// Подключение к БД
	$connect = new mysqli($host, $username, $password, $database);
	if ($connect->connect_errno) {printf("Не удалось подключиться: %s\n", $connect->connect_error); exit();}
	$connect->set_charset("utf8");
	// Выбор действия (поиск или копирование страниц)
	if($_POST['action'] == 'search'){
		// Получаем условия поиска
		if(!empty($_POST['where'])){
			$where = strip_tags($_POST['where']);
			$message.= "<p>Поиск по фразе: <strong>".$where."</strong></p>\n";
		} else{
			$message.= "<p class=\"error\">Пустой поисковый запрос</p>\n";
		}
		// Формируем запрос к БД и преобразуем в массив
		$query = "SELECT * FROM `".$table."` WHERE (`title` LIKE '%".$where."%' OR `content` LIKE '%".$where."%') ORDER BY `id` ASC";
		$resourses = $connect->query($query);
		$count = $resourses->num_rows;
		while($row = $resourses->fetch_array()) {
			$rows[] = $row;
		}
		// Выводим результат
		if ($count > 0) {
			$message.= "<p>Найдено страниц: <strong>".$count."</strong></p>\n<ul>\n";
		} else {
			$message.= "<p class=\"error\">Страниц не найдено, попробуйте изменить запрос.</p>\n";
		}
		foreach ($rows as $row) {
			// Получаем родителей
			$parent_id = $row['parent'];
			$parents = "";
			while($parent_id > 0){
				if(!empty($parents)) {
					$parents .= " &larr; ";
				}
				$query_parent = "SELECT `id`, `title`, `parent` FROM `".$table."` WHERE `id` = '".$parent_id."'";
				$resourses_parent = $connect->query($query_parent);
				$row_parent = $resourses_parent->fetch_array();
				$parents .= $row_parent['title']." (id: ".$row_parent['id'].")";
				$parent_id = $row_parent['parent'];
			}
			if(!empty($parents)) {
				$parents = " &larr; ".$parents;
			}
			$message.= "<li><strong>".$row['title']."</strong> (id: <strong>".$row['id']."</strong>)".$parents."</li>\n";
		}
		$message.= "</ul>\n";
	} elseif ($_POST['action'] == 'copy') {
		if($connect->query("TRUNCATE TABLE `".$table."`")) {$message.= "<p>Старые записи успешно удалены.</p>\n";} // Очистка таблицы перед копированием
		// Получаем записи для копирования
		$query = "SELECT `ID`, `post_title`, `post_content`, `post_parent` FROM `wp_posts` WHERE `post_type` LIKE 'page' ORDER BY `ID` ASC";
		$resourses = $connect->query($query);
		$count = $resourses->num_rows;
		$message.= "<p>Найдено страниц: <strong>".$count."</strong></p>\n";
		// Преобразуем в массив
		while($row = $resourses->fetch_array()) {
			$rows[] = $row;
		}
		// Экспортируем в новую таблицу
		$count = 0;
		foreach ($rows as $row) {
			$query = "INSERT `".$table."` VALUES ('".$row['ID']."', '".$row['post_title']."', '".$row['post_content']."', '".$row['post_parent']."')";
			if($connect->query($query)){$count++;}
		}
		$message.= "<p>Успешно экспортировано: <strong>".$count."</strong></p>\n";
	}
	// Закрытие
	$resourses->free();
	$connect->close();
	echo $message; // Выводим результат
?>