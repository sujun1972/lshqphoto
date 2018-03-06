<?php
/**
 * @file
 * The primary PHP file for this theme.
 */

function lishui_colorbox_image_formatter($variables) {
    static $gallery_token = NULL;
    $item = $variables['item'];
    $entity_type = $variables['entity_type'];
    $entity = $variables['entity'];
    $field = $variables['field'];
    $settings = $variables['display_settings'];

    $image = array(
        'path' => $item['uri'],
        'alt' => isset($item['alt']) ? $item['alt'] : '',
        'title' => isset($item['title']) ? $item['title'] : '',
        'style_name' => $settings['colorbox_node_style'],
    );

    if ($variables['delta'] == 0 && !empty($settings['colorbox_node_style_first'])) {
        $image['style_name'] = $settings['colorbox_node_style_first'];
    }

    if (isset($item['width']) && isset($item['height'])) {
        $image['width'] = $item['width'];
        $image['height'] = $item['height'];
    }

    if (isset($item['attributes'])) {
        $image['attributes'] = $item['attributes'];
    }

    // Allow image attributes to be overridden.
    if (isset($variables['item']['override']['attributes'])) {
        foreach (array('width', 'height', 'alt', 'title') as $key) {
            if (isset($variables['item']['override']['attributes'][$key])) {
                $image[$key] = $variables['item']['override']['attributes'][$key];
                unset($variables['item']['override']['attributes'][$key]);
            }
        }
        if (isset($image['attributes'])) {
            $image['attributes'] = $variables['item']['override']['attributes'] + $image['attributes'];
        }
        else {
            $image['attributes'] = $variables['item']['override']['attributes'];
        }
    }

    $entity_title = entity_label($entity_type, $entity);

    switch ($settings['colorbox_caption']) {
        case 'auto':
            // If the title is empty use alt or the entity title in that order.
            if (!empty($image['title'])) {
                $caption = $image['title'];
            }
            elseif (!empty($image['alt'])) {
                $caption = $image['alt'];
            }
            elseif (!empty($entity_title)) {
                $caption = $entity_title;
            }
            else {
                $caption = '';
            }
            break;

        case 'title':
            $caption = $image['title'];
            break;

        case 'alt':
            $caption = $image['alt'];
            break;

        case 'node_title':
            $caption = $entity_title;
            break;

        case 'custom':
            $caption = token_replace($settings['colorbox_caption_custom'], array($entity_type => $entity, 'file' => (object) $item), array('clear' => TRUE));
            break;

        default:
            $caption = '';
    }

    // Shorten the caption for the example styles or when caption shortening is active.
    $colorbox_style = variable_get('colorbox_style', 'default');
    $trim_length = variable_get('colorbox_caption_trim_length', 75);
    if (((strpos($colorbox_style, 'colorbox/example') !== FALSE) || variable_get('colorbox_caption_trim', 0)) && (drupal_strlen($caption) > $trim_length)) {
        $caption = drupal_substr($caption, 0, $trim_length - 5) . '...';
    }

    // Build the gallery id.
    list($id, $vid, $bundle) = entity_extract_ids($entity_type, $entity);
    $entity_id = !empty($id) ? $entity_type . '-' . $id : 'entity-id';
    switch ($settings['colorbox_gallery']) {
        case 'post':
            $gallery_id = 'gallery-' . $entity_id;
            break;

        case 'page':
            $gallery_id = 'gallery-all';
            break;

        case 'field_post':
            $gallery_id = 'gallery-' . $entity_id . '-' . $field['field_name'];
            break;

        case 'field_page':
            $gallery_id = 'gallery-' . $field['field_name'];
            break;

        case 'custom':
            $gallery_id = $settings['colorbox_gallery_custom'];
            break;

        default:
            $gallery_id = '';
    }

    // If gallery id is not empty add unique per-request token to avoid images being added manually to galleries.
    if (!empty($gallery_id) && variable_get('colorbox_unique_token', 1)) {
        // Check if gallery token has already been set, we need to reuse the token for the whole request.
        if (is_null($gallery_token)) {
            // We use a short token since randomness is not critical.
            $gallery_token = drupal_random_key(8);
        }
        $gallery_id = $gallery_id . '-' . $gallery_token;
    }

    if ($style_name = $settings['colorbox_image_style']) {
        $path = image_style_url($style_name, $image['path']);
    }
    else {
        $path = file_create_url($image['path']);
    }

    // return theme('colorbox_imagefield', array('image' => $image, 'path' => $path, 'title' => $caption, 'gid' => $gallery_id));
    $img_node_url_options = array('absolute' => TRUE);
    $img_node_url = url('node/' . $entity->nid, $img_node_url_options);
    return theme('colorbox_imagefield', array('url' => $img_node_url, 'image' => $image, 'path' => $path, 'title' => $caption.'|||'. $entity->nid, 'gid' => $gallery_id));
}


function lishui_colorbox_imagefield($variables) {
    $class = array('colorbox');

    if ($variables['image']['style_name'] == 'hide') {
        $image = '';
        $class[] = 'js-hide';
    }
    elseif (!empty($variables['image']['style_name'])) {
        $image = theme('image_style', $variables['image']);
    }
    else {
        $image = theme('image', $variables['image']);
    }

    $options = drupal_parse_url($variables['path']);
    $options += array(
        'html' => TRUE,
        'attributes' => array(
            'title' => $variables['title'],
            // 'class' => $class,
            'class' => implode(' ', $class),
            'rel' => $variables['gid'],
            'url' => $variables['url'],
            'data-colorbox-gallery' => $variables['gid'],
            'data-cbox-img-attrs' => '{"title": "' . $variables['image']['title'] . '", "alt": "' . $variables['image']['alt'] . '"}',
        ),
    );

    return l($image, $options['path'], $options);
}

function lishui_preprocess_user_profile(&$variables) {
    // dsm($variables);
    $account = $variables['elements']['#account'];
    //Add the user ID into the user profile as a variable
    $variables['user_id'] = $account->uid;
    // Helpful $user_profile variable for templates.
    foreach (element_children($variables['elements']) as $key) {
        $variables['user_profile'][$key] = $variables['elements'][$key];
    }

    // Preprocess fields.
    field_attach_preprocess('user', $account, $variables['elements'], $variables);

}