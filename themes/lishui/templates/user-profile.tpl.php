<div class="user-info">
    <div class="picture">
        <?php
            if ($elements['#account']->picture) {
                print render($user_profile['user_picture']);
            } else {
                $custom_default_image_path = 'public://pictures/default.png';
                print theme('image_style', array('path' => $custom_default_image_path , 'style_name' => 'thumbnail'));
            }
        ?>
    </div>
    <div class="detail">
        <?php print "<b>加入日期：</b>" . format_date($elements['#account']->created, $format = 'Y/m/d');?>
    </div>
</div>
<h4>最新作品</h4>
<?php
echo views_embed_view('photo', $display_id = 'block_user');


