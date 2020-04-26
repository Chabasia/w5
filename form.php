<html lang="ru">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Задание 5</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="main.css">
  </head>
  <body>
    <form id="form" action="" method="POST">
      <!-- Вывод сообщений об отправке -->
      <?php
      if ($messages['save'] != '') {
        print '<div class="alert alert-success">'.$messages["save"].$messages['savelogin'].'</div>';
      }
       if ($messages['notsave'] != '') {
        print '<div class="alert alert-danger">'.$messages["notsave"].'</div>';
      }
      ?>
      <!-- Имя -->
      <div class="form-group">
        <label for="nameInput">Имя</label>
        <input id="nameInput" class="form-control <?php if ($errors['name']) print 'is-invalid'; else print 'is-valid' ?>" type="text" name="name" placeholder="Ваше имя" value="<?php print $values['name']; ?>" />
        <?php print '<p class="invalid-feedback">'.$messages['name'].'</p>'; ?>
      </div>
      <!-- Email -->
      <div class="form-group">
        <label for="emailInput">Email</label>
        <input id="emailInput" class="form-control <?php if ($errors['email']) print 'is-invalid'; else print 'is-valid' ?>" type="email" name="email" placeholder="Ваше Email" value="<?php print $values['email']; ?>" />
        <?php print '<p class="invalid-feedback">'.$messages['email'].'</p>'; ?>
      </div>
      <!-- Year -->
      <div class="form-group">
        <label for="yearSelect">Год рождения</label>
            <select class="form-control is-valid" name="year" id="yearSelect">
              <?php
              for ($i = 2014; $i > 1955; $i--) {
                print('<option value="'.$i.'"');
                if ($values['year'] == $i) {
                  print('selected');
                }
                print('>'.$i.'</option>');
              }
              ?>
            </select>
      </div>
      <!-- Sex -->
      <div class="form-group">
        <label>Пол</label>
        <div class="control">
          <label class="radio">
            <input type="radio" name="sex" value="male" <?php if ($values['sex'] == 'male') print(' checked'); ?> />
            Мужской
          </label>
          <label class="radio">
            <input type="radio" name="sex" value="female" <?php if ($values['sex'] == 'female') print(' checked'); ?> />
            Женский
          </label>
        </div>
      </div>
      <!-- Limbs -->
      <div class="form-group">
        <label>Количество конечностей</label>
        <div class="control">
          <label class="radio">
            <input type="radio" name="limbs" value="2" <?php if ($values['limbs'] == 2) print(" checked "); ?> />
            2
          </label>
          <label class="radio">
            <input type="radio" name="limbs" value="4" <?php if ($values['limbs'] == 4) print(" checked "); ?>  />
            4
          </label>
          <label class="radio">
            <input type="radio" name="limbs" value="8" <?php if ($values['limbs'] == 8) print(" checked "); ?>  />
            8
          </label>
        </div>
      </div>
      <!-- Powers -->
      <div class="form-group">
        <label for="limbsSelect">Сверхспособности</label>
          <select class="form-control <?php if ($errors['powers']) print 'is-invalid'; else print 'is-valid' ?>" id="limbsSelect" name="powers[]" multiple size="3">
          <?php
          foreach ($powers as $key => $value) {
            $selected = empty($values['powers'][$key]) ? '' : ' selected="selected" ';
            printf('<option value="%s",%s>%s</option>', $key, $selected, $value);
          }
          ?>
          </select>
        <?php print '<p class="invalid-feedback">'.$messages['powers'].'</p>'; ?>
      </div>
      <!-- Bio -->
      <div class="form-group">
        <label for="bioText">Биография</label>
        <textarea id="bioText" name="bio" class="form-control <?php if ($errors['bio']) print 'is-invalid'; else print 'is-valid' ?>" placeholder="Напишите здесь немного о себе..."><?php print $values['bio']; ?></textarea>
        <?php print '<p class="invalid-feedback">'.$messages['bio'].'</p>'; ?>
      </div>
      <!-- Checkbox -->
      <div class="form-group">
        <div class="control">
          <label class="checkbox">
            <input type="checkbox" name="check" value="ok">
              С <a href="#" class="has-text-info">контрактом</a> ознакомлен(а).
          </label>
        </div>
        <?php print '<p class="invalid-feedback">'.$messages['check'].'</p>'; ?>
      </div>
      <!-- Button -->
      <div class="form-group is-grouped">
        <div class="control">
          <button name="btn" type="submit" class="mybtn btn btn-success" value="ok">Отправить</button>
        </div>
      </div>
    </form>
    <!-- ./Form -->
  </body>
</html>
