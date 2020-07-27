<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 14.06.2019
 * Time: 17:50
 */
?>
<div class="wrap">
  <h2><?php echo get_admin_page_title(); ?></h2>

  <div class="page_body">
    <?php if($token):  ?>
    <h4>Токен существует</h4>
    <?php else: ?>
    <a href="<?= $authUrl; ?>" target="_blank" style="display: inline-block;margin-bottom: 16px;">Авторизоваться</a>
    <div class="options">
      <div><input type="text" size="60" name="google-api" value="" placeholder="Token" /></div>
      <button style="margin-top: 16px;" type="button" name="submit" class="button button-primary api-submit"><?= __('Сохранить') ?></button>
    </div>
    <?php endif; ?>

  </div>
</div>
