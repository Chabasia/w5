<?php

/**
 * Файл login.php для не авторизованного пользователя выводит форму логина.
 * При отправке формы проверяет логин/пароль и создает сессию,
 * записывает в нее логин пользователя.
 * После авторизации пользователь перенаправляется на главную страницу
 * для изменения ранее введенных данных.
 **/

// Отправляем браузеру правильную кодировку,
// файл login.php должен быть в кодировке UTF-8 без BOM.
header('Content-Type: text/html; charset=UTF-8');

// Начинаем сессию.
session_start();

// В суперглобальном массиве $_SESSION хранятся переменные сессии.
// Будем сохранять туда логин после успешной авторизации.
// Если в сесси уже существует логин, значит пользователь уже авторизован.
if (!empty($_SESSION['login'])) {
  // Перенаправлем его в форму.
  header('Location: ./');
}

// В суперглобальном массиве $_SERVER PHP сохраняет некторые заголовки запроса HTTP
// и другие сведения о клиненте и сервере, например метод текущего запроса $_SERVER['REQUEST_METHOD'].
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
?>

<form action="" method="post">
  <input name="login" placeholder="Ваш логин" />
  <input name="pass" placeholder="Ваш пароль" />
  <input type="submit" value="Войти" />
</form>

<?php
}
// Иначе, если запрос был методом POST, т.е. нужно сделать авторизацию с записью логина в сессию.
else {

  $login = $_POST['login'];
  $pass =  hash('sha256', $_POST['pass'], false);

  $db_user = 'u17334';   // Логин БД
  $db_pass = '4897115';  // Пароль БД

  $db = new PDO('mysql:host=localhost;dbname=u17334', $db_user, $db_pass, array(
    PDO::ATTR_PERSISTENT => true
  ));

  try {

    $stmt = $db->prepare("SELECT * FROM web5 WHERE login = ?");
    $stmt->execute(array(
      $login
    ));
    $user = $stmt->fetch();

    // Сравнием текущий хэш пароля с тем, что достали из базы.
    if ($pass == $user['pass']) {
      $_SESSION['login'] = $login;
    }
    else {
      echo "Неверные данные. Повторите попытку.";
      exit();
    }

  }
  catch(PDOException $e) {
    // При возникновении ошибки получения данных из БД, выводим информацию
    // об ошибке пользователю и прекращаем работу скрипта.
    echo 'Ошибка: ' . $e->getMessage();
    exit();
  }

  // Делаем перенаправление.
  header('Location: ./');
}
