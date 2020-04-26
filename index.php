<?php
/**
 * Реализовать возможность входа с паролем и логином с использованием
 * сессии для изменения отправленных данных в предыдущей задаче,
 * пароль и логин генерируются автоматически при первоначальной отправке формы.
 */

// Отправляем браузеру правильную кодировку,
// файл index.php должен быть в кодировке UTF-8 без BOM.
header('Content-Type: text/html; charset=UTF-8');

$db_user = 'u17334';   // Логин БД
$db_pass = '4897115';  // Пароль БД

$db = new PDO('mysql:host=localhost;dbname=u17334', $db_user, $db_pass, array(
  PDO::ATTR_PERSISTENT => true
));

// В суперглобальном массиве $_SERVER PHP сохраняет некторые заголовки запроса HTTP
// и другие сведения о клиненте и сервере, например метод текущего запроса $_SERVER['REQUEST_METHOD'].
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  // Массив для временного хранения сообщений пользователю.
  $messages = array();
  $messages['save'] = '';
  $messages['notsave'] = '';
  $messages['name'] = '';
  $messages['email'] = '';
  $messages['powers'] = '';
  $messages['bio'] = '';
  $messages['check'] = '';

  // В суперглобальном массиве $_COOKIE PHP хранит все имена и значения куки текущего запроса.
  // Выдаем сообщение об успешном сохранении.
  if (!empty($_COOKIE['save'])) {
    // Удаляем куки, указывая время устаревания в прошлом.
    setcookie('save', '', 100000);
    setcookie('login', '', 100000);
    setcookie('pass', '', 100000);
    // Выводим сообщение пользователю.
    $messages['save'] = 'Спасибо, результаты отправлены на сервер.';
    // Если в куках есть пароль, то выводим сообщение.
    if (!empty($_COOKIE['pass'])) {
      $messages['savelogin'] = sprintf(' Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong>
        и паролем <strong>%s</strong> для изменения данных.',
        strip_tags($_COOKIE['login']),
        strip_tags($_COOKIE['pass']));
    }
  }

  // Выдаем сообщение об ошибке сохранения.
  if (!empty($_COOKIE['notsave'])) {
    // Удаляем куки, указывая время устаревания в прошлом.
    setcookie('notsave', '', 100000);
    $messages['notsave'] = strip_tags($_COOKIE['notsave']);
  }

  // Складываем признаки ошибок в массив.
  $errors = array();
  $errors['name'] = empty($_COOKIE['name_error']) ? '' : $_COOKIE['name_error'];
  $errors['email'] = empty($_COOKIE['email_error']) ? '' : $_COOKIE['email_error'];
  $errors['powers'] = empty($_COOKIE['powers_error']) ? '' : $_COOKIE['powers_error'];
  $errors['bio'] = empty($_COOKIE['bio_error']) ? '' : $_COOKIE['bio_error'];
  $errors['check'] = empty($_COOKIE['check_error']) ? '' : $_COOKIE['check_error'];

  // Проверка на ошибки в имени.
  if ($errors['name'] == 'null') {
    setcookie('name_error', '', 100000);
    $messages['name'] = 'Заполните имя.';
  }
  else if ($errors['name'] == 'incorrect') {
      setcookie('name_error', '', 100000);
      $messages['name'] = 'Недопустимые символы. Введите имя заново.';
  }

  // Проверка ошибок в email.
  if ($errors['email']) {
    setcookie('email_error', '', 100000);
    $messages['email'] = 'Заполните почту.';
  }

  // Проверка ошибок в способностях.
  if ($errors['powers']) {
    setcookie('powers_error', '', 100000);
    $messages['powers'] = 'Выберите хотя бы одну сверхспособность.';
  }

  // Проверка ошибок с биографии.
  if ($errors['bio']) {
    setcookie('bio_error', '', 100000);
    $messages['bio'] = 'Напишите что-нибудь о себе.';
  }

  // Проверка ошибок согласия с контрактом.
  if ($errors['check']) {
    setcookie('check_error', '', 100000);
    $messages['check'] = 'Вы не можете отправить форму не согласившись с контрактом!';
  }

  // Создаем массив для способностей.
  $powers = array();
  $powers['lv'] = "Левитация";
  $powers['tp'] = "Телепортация";
  $powers['wl'] = "Хождение сквозь стены";

  // Складываем предыдущие значения полей в массив, если есть.
  // При этом санитизуем все данные для безопасного отображения в браузере.
  $values = array();
  $values['name'] = empty($_COOKIE['name_value']) ? '' : strip_tags($_COOKIE['name_value']);
  $values['email'] = empty($_COOKIE['email_value']) ? '' : strip_tags($_COOKIE['email_value']);
  $values['year'] = empty($_COOKIE['year_value']) ? '' : strip_tags($_COOKIE['year_value']);
  $values['sex'] = empty($_COOKIE['sex_value']) ? 'male' : strip_tags($_COOKIE['sex_value']);
  $values['limbs'] = empty($_COOKIE['limbs_value']) ? '4' : strip_tags($_COOKIE['limbs_value']);
  $values['bio'] = empty($_COOKIE['bio_value']) ? '' : strip_tags($_COOKIE['bio_value']);
  $powers_value = empty($_COOKIE['powers_value']) ? '' : json_decode($_COOKIE['powers_value']);

  $values['powers'] = [];

  if (isset($powers_value) && is_array($powers_value)) {
    foreach ($powers_value as $power) {
      if (!empty($powers[$power])) {
        $values['powers'][$power] = $power;
      }
    }
  }

  // Если нет предыдущих ошибок ввода, есть кука сессии, начали сессию и
  // ранее в сессию записан факт успешного логина.
  if (!empty($_COOKIE[session_name()]) && session_start() && !empty($_SESSION['login'])) {

    $messages['save'] = ' ';
    $messages['savelogin'] = 'Вы вошли с логином '.$_SESSION['login'];

    try {

      $stmt = $db->prepare("SELECT * FROM web5 WHERE login = ?");
      $stmt->execute(array(
        $_SESSION['login']
      ));
      $user_data = $stmt->fetch();

      // Инициализируем $values значениями из массива полученного из БД
      // предварительно их санитизовав.
      $values['name'] = strip_tags($user_data['name']);
      $values['email'] = strip_tags($user_data['email']);
      $values['year'] = strip_tags($user_data['age']);
      $values['sex'] = strip_tags($user_data['sex']);
      $values['limbs'] = strip_tags($user_data['limbs']);
      $values['bio'] = strip_tags($user_data['bio']);
      $powers_value = explode(", ", $user_data['powers']);

      $values['powers'] = [];
      foreach ($powers_value as $power) {
        if (!empty($powers[$power])) {
          $values['powers'][$power] = $power;
        }
      }

    } catch(PDOException $e) {
      // При возникновении ошибки получения данных из БД, выводим информацию
      // об ошибке пользователю и прекращаем работу скрипта.
      setcookie('notsave', 'Ошибка: ' . $e->getMessage());
      exit();
    }
  }

  // Включаем содержимое файла form.php.
  // В нем будут доступны переменные $messages, $errors и $values для вывода
  // сообщений, полей с ранее заполненными данными и признаками ошибок.
  include('form.php');
}
// Иначе, если запрос был методом POST, т.е. нужно проверить данные и сохранить
// в базу данных.
else {
  // Проверяем ошибки.
  $errors = FALSE;
  if (empty($_POST['name'])) {
    // Выдаем куку на день с флажком об ошибке в поле name.
    setcookie('name_error', 'null', time() + 24 * 60 * 60);
    // Помечаем наличие ошибки.
    $errors = TRUE;
  }
  else if (!preg_match("#^[aA-zZ0-9-]+$#", $_POST["name"])) {
      setcookie('name_error', 'incorrect', time() + 24 * 60 * 60);
      $errors = TRUE;
  }
  else {
    // Сохраняем ранее введенное в форму значение на месяц.
    setcookie('name_value', $_POST['name'], time() + 30 * 24 * 60 * 60);
  }

  // Аналогично проверяем ошибки для всех остальных полей.
  if (empty($_POST['email'])) {
    // Выдаем куку на день с флажком об ошибке в поле name.
    setcookie('email_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  else {
    // Сохраняем ранее введенное в форму значение на месяц.
    setcookie('email_value', $_POST['email'], time() + 30 * 24 * 60 * 60);
  }

  $powers = array();
  foreach ($_POST['powers'] as $key => $value) {
      $powers[$key] = $value;
  }

  if (!sizeof($powers)) {
    setcookie('powers_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  else {
    setcookie('powers_value', json_encode($powers), time() + 30 * 24 * 60 * 60);
  }

  if (empty($_POST['bio'])) {
    // Выдаем куку на день с флажком об ошибке в поле name.
    setcookie('bio_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  else {
    // Сохраняем ранее введенное в форму значение на месяц.
    setcookie('bio_value', $_POST['bio'], time() + 30 * 24 * 60 * 60);
  }

  if (empty($_POST['check'])) {
    // Выдаем куку на день с флажком об ошибке в поле name.
    setcookie('check_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  setcookie('year_value', $_POST['year'], time() + 30 * 24 * 60 * 60);
  setcookie('sex_value', $_POST['sex'], time() + 30 * 24 * 60 * 60);
  setcookie('limbs_value', $_POST['limbs'], time() + 30 * 24 * 60 * 60);

  if ($errors) {
    // При наличии ошибок перезагружаем страницу и завершаем работу скрипта.
    header('Location: index.php');
    exit();
  }
  else {
    // Иначе удаляем Cookies с признаками ошибок.
    setcookie('name_error', '', 100000);
    setcookie('name_error', '', 100000);
    setcookie('email_error', '', 100000);
    setcookie('powers_error', '', 100000);
    setcookie('bio_error', '', 100000);
    setcookie('check_error', '', 100000);
  }

  // Проверяем меняются ли ранее сохраненные данные или отправляются новые.
  if (!empty($_COOKIE[session_name()]) && session_start() && !empty($_SESSION['login'])) {

    try {

      $stmt = $db->prepare("UPDATE web5 SET name = ?, email = ?, age = ?, sex = ?, limbs = ?, powers = ?, bio = ? WHERE login = ?");
      $stmt->execute(array(
        $_POST['name'],
        $_POST['email'],
        $_POST['year'],
        $_POST['sex'],
        $_POST['limbs'],
        implode(', ', $_POST['powers']),
        $_POST['bio'],
        $_SESSION['login']
      ));
    } catch(PDOException $e) {
      // При возникновении ошибки получения данных из БД, выводим информацию
      // об ошибке пользователю и прекращаем работу скрипта.
      setcookie('notsave', 'Ошибка: ' . $e->getMessage());
      exit();
    }

  }
  else {
    // Иначе, если отправляются новые данные.
    // Генерируем уникальный логин и пароль.
    $login = uniqid("id");
    $pass = rand(123456, 999999);
    // Сохраняем в Cookies.
    setcookie('login', $login);
    setcookie('pass', $pass);

    try {

      $stmt_form = $db->prepare("INSERT INTO web5 SET login = ?, pass = ?, name = ?, email = ?, age = ?, sex = ?, limbs = ?, powers = ?, bio = ?");
      $stmt_form->execute(array(
        $login,
        hash('sha256', $pass, false),
        $_POST['name'],
        $_POST['email'],
        $_POST['year'],
        $_POST['sex'],
        $_POST['limbs'],
        implode(', ', $_POST['powers']),
        $_POST['bio']
      ));
    } catch(PDOException $e) {
      // При возникновении ошибки получения данных из БД, выводим информацию
      // об ошибке пользователю и прекращаем работу скрипта.
      setcookie('notsave', 'Ошибка: ' . $e->getMessage());
      exit();
    }
  }

  // Сохраняем куку с признаком успешного сохранения.
  setcookie('save', '1');

  // Делаем перенаправление.
  header('Location: ./');
}
