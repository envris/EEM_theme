<?php

/**
 * @file
 * template.php
 */

/**
 * Implements hook_html_head_alter().
 */
function doeeem_theme_html_head_alter(&$head_elements) {
  // Mobile Viewport.
  $head_elements['viewport'] = array(
    '#type' => 'html_tag',
    '#tag' => 'meta',
    '#attributes' => array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'),
  );
  // IE Latest Browser.
  $head_elements['ie_view'] = array(
    '#type' => 'html_tag',
    '#tag' => 'meta',
    '#attributes' => array('http-equiv' => 'x-ua-compatible', 'content' => 'ie=edge'),
  );
}

/**
 * Implements hook_js_alter().
 */
function doeeem_theme_js_alter(&$javascript) {
  $javascript['misc/jquery.js']['data'] = drupal_get_path('theme', 'doeeem_theme') . '/vendor/jquery/jquery-3.1.1.min.js';
}

/**
 * Implements hook_preprocess_html().
 */
function doeeem_theme_preprocess_html(&$variables) {
  drupal_add_js("(function(h) {h.className = h.className.replace('no-js', '') })(document.documentElement);", array('type' => 'inline', 'scope' => 'header'));
  drupal_add_js('jQuery.extend(Drupal.settings, { "pathToTheme": "' . path_to_theme() . '" });', 'inline');
  // Drupal forms.js does not support new jQuery. Migrate library needed.
  drupal_add_js(drupal_get_path('theme', 'doeeem_theme') . '/vendor/jquery/jquery-migrate-1.2.1.min.js');
}

/**
 * Implements hook_preprocess_field().
 */
function doeeem_theme_preprocess_field(&$variables) {
  // Bean 'Image and Text' field 'Link To' to show 'Read [title]' text.
  if ($variables['element']['#field_name'] === 'field_link_to' && $variables['element']['#bundle'] === 'image_and_text') {
    if (!empty($variables['items'][0]) && !empty($variables['element']['#object']->title)) {
      // This only applies if field has a non-configurable title.
      if ($variables['items'][0]['#field']['settings']['title'] === 'none') {
        $variables['items'][0]['#element']['title'] = t('Read !title', array('!title' => $variables['element']['#object']->title));
      }
    }
  }
  if (theme_get_setting('doeeem_theme_override_image_styles') == 1) {
    // Define custom image style for image banners on home page.
    if ($variables['element']['#field_name'] === 'field_slide_image') {
      if ($variables['items'][0]['#image_style'] === 'feature_article') {
        $variables['items'][0]['#image_style'] = 'doeeem_theme_banner';
      }
    }
    // Define custom image style for thumbnails on news / blogs / etc.
    elseif ($variables['element']['#field_name'] === 'field_thumbnail') {
      $image_style = $variables['items'][0]['#image_style'];
      if ($image_style === 'medium' || $image_style === 'thumbnail') {
        $variables['items'][0]['#image_style'] = 'doeeem_theme_thumbnail';
      }
    }
    // Define custom image style for views.
    elseif ($variables['element']['#field_name'] === 'field_image') {
      if (!empty($variables['items'][0]['#image_style']) && $variables['items'][0]['#image_style'] === 'medium') {
        $variables['items'][0]['#image_style'] = 'doeeem_theme_thumbnail';
      }
    }
  }

  // Add a theme suggestion for Measure content type full view.
  $element = $variables['element'];
  if ($element['#bundle'] == "measure" && $element['#view_mode'] == "full") {
    $variables['theme_hook_suggestions'][] = "field__measure__full";
  }
}

/**
 * Implements hook_views_pre_render().
 */
function doeeem_theme_views_pre_render(&$variables) {
  if (theme_get_setting('doeeem_theme_override_image_styles') == 1) {
    if ($variables->name === 'footer_teaser') {
      $len = count($variables->result);
      for ($i = 0; $i < $len; $i++) {
        if (!empty($variables->result[$i]->field_field_image)) {
          // Define custom image style for thumbnails on footer_teaser.
          if ($variables->result[$i]->field_field_image[0]['rendered']['#image_style'] == 'blog_teaser_thumbnail') {
            $variables->result[$i]->field_field_image[0]['rendered']['#image_style'] = 'doeeem_theme_thumbnail';
          }
        }
      }
    }
  }
}

/**
 * Implements hook_image_styles_alter().
 */
function doeeem_theme_image_styles_alter(&$styles) {
  if (theme_get_setting('doeeem_theme_override_image_styles') == 1) {
    $styles['doeeem_theme_banner'] = array(
      'label' => 'govCMS UI-KIT - Banner',
      'name' => 'doeeem_theme_banner',
      'storage' => IMAGE_STORAGE_NORMAL,
      'effects' => array(
        array(
          'label' => 'Scale and crop',
          'name' => 'image_scale_and_crop',
          'data' => array(
            'width' => 1650,
            'height' => 440,
            'upscale' => 1,
          ),
          'effect callback' => 'image_scale_and_crop_effect',
          'dimensions callback' => 'image_resize_dimensions',
          'form callback' => 'image_resize_form',
          'summary theme' => 'image_resize_summary',
          'module' => 'image',
          'weight' => 0,
        ),
      ),
    );
    $styles['doeeem_theme_thumbnail'] = array(
      'label' => 'govCMS UI-KIT - Thumbnail',
      'name' => 'doeeem_theme_thumbnail',
      'storage' => IMAGE_STORAGE_NORMAL,
      'effects' => array(
        array(
          'label' => 'Scale and crop',
          'name' => 'image_scale_and_crop',
          'data' => array(
            'width' => 370,
            'height' => 275,
            'upscale' => 1,
          ),
          'effect callback' => 'image_scale_and_crop_effect',
          'dimensions callback' => 'image_resize_dimensions',
          'form callback' => 'image_resize_form',
          'summary theme' => 'image_resize_summary',
          'module' => 'image',
          'weight' => 0,
        ),
      ),
    );
  }
  return $styles;
}

/**
 * Implements hook_preprocess_node().
 */
function doeeem_theme_preprocess_node(&$variables) {
  if ($variables['view_mode'] === 'teaser' || $variables['view_mode'] === 'compact') {
    // Apply thumbnail class to node teaser view if image exists.
    $has_thumb = !empty($variables['content']['field_thumbnail']);
    $has_image = !empty($variables['content']['field_image']);
    $has_featured_image = !empty($variables['content']['field_feature_image']);
    if ($has_thumb || $has_image || $has_featured_image) {
      $variables['classes_array'][] = 'has-thumbnail';
    }
  }

  if ($variables['type'] === 'webform') {
    // Hide submitted date on webforms.
    $variables['display_submitted'] = FALSE;
  }

  if ($variables['type'] == "data") {
    // Display raw markup for the embed code.
    if (!empty($variables['content']['field_embed_code'])) {
      $variables['content']['field_embed_code'][0]['#markup'] = $variables['content']['field_embed_code']['#items'][0]['value'];
    }
  }
}

/**
 * Implements theme_breadcrumb().
 */
function doeeem_theme_breadcrumb($variables) {
  $breadcrumb = $variables['breadcrumb'];
  $output = '';

  if (!empty($breadcrumb)) {
    // Build the breadcrumb trail.
    $output = '<nav class="breadcrumbs--inverted" role="navigation" aria-label="breadcrumb">';
    $output .= '<ul><li>' . implode('</li><li>', $breadcrumb) . '</li></ul>';
    $output .= '</nav>';
  }

  return $output;
}

/**
 * Implements hook_form_alter().
 */
function doeeem_theme_form_alter(&$form, &$form_state, $form_id) {
  if ($form_id === 'search_api_page_search_form_default_search') {
    // Global header form.
    $form['keys_1']['#attributes']['placeholder'] = t('Type search term here');
    $form['keys_1']['#title'] = t('Search field');
  }
  elseif ($form_id === 'search_api_page_search_form') {
    // Search page (above results) form.
    $form['form']['keys_1']['#title'] = t('Type search term here');
  }
  if ($form_id === 'search_form') {
    // Search form on page not found (404 page).
    $form['basic']['keys']['#title'] = t('Type search term here');
  }
}

/**
 * Implements theme_preprocess_search_api_page_result().
 */
function doeeem_theme_preprocess_search_api_page_result(&$variables) {
  // Strip out HTML tags from search results.
  $variables['snippet'] = strip_tags($variables['snippet']);
  // Remove the author / date from the result display.
  $variables['info'] = '';
}

/**
 * Implements theme_preprocess_search_result().
 */
function doeeem_theme_preprocess_search_result(&$variables) {
  // Strip out HTML tags from search results (404 page).
  $variables['snippet'] = strip_tags($variables['snippet']);
  // Remove the author / date from the result display (404 page).
  $variables['info'] = '';
}

/**
 * Generate the markup for the themes submenu.
 *
 * @return array
 *  The markup and a count of the number of themes found.
 */
function _doeeem_theme_measures_submenu_markup() {
  // Get theme terms.
  $voc = taxonomy_vocabulary_machine_name_load('theme');
  $terms = taxonomy_get_tree($voc->vid, 0, NULL, TRUE);

  // Create the submenu markup.
  $sub_menu = '';
  foreach ($terms as $term) {
    if (!empty($term->field_theme_status) && $term->field_theme_status['und'][0]['value'] === "1") {
      $sub_menu .= '<li>' . l($term->name, 'taxonomy/term/' . $term->tid) . "</li>\n";
    }
  }
  return [$sub_menu, count($terms)];
}

/**
 * Implements theme_preprocess_menu_block_wrapper().
 */
function doeeem_theme_preprocess_menu_block_wrapper(&$vars) {
  // Add children to the Essential Environmental Measures link in the footer.
  if ($vars['delta'] == "govcms_menu_block-footer") {
    $parent_menu = &$vars['content'][903];
    list($sub_menu, ) = _doeeem_theme_measures_submenu_markup();
    $parent_menu['#below'] = [
      '#markup' => '<ul class="menu">' . $sub_menu . '</ul>'
    ];
  }
}

/**
 * Implements theme_preprocess_superfish_menu_item().
 */
function doeeem_theme_preprocess_superfish_menu_item(&$vars) {
  $element = &$vars['element'];
  // Add children to the Essential Environmental Measures link in main menu.
  if ($element['item']['link']['link_path'] == "node/6") {
    list($sub_menu, $theme_count) = _doeeem_theme_measures_submenu_markup();
    $element['below'] = $sub_menu;
    // Add Superfish submenu classes.
    $classes = $element['attributes']['class'];
    $element['attributes']['class'] = str_replace('sf-no-children', "sf-total-children-$theme_count sf-parent-children-0 sf-single-children-$theme_count menuparent", $classes);
  }
}

/**
 * Defines the menu link theme for Landing Page submenu links.
 */
function doeeem_theme_menu_link__menu_block__1(&$vars) {
  $element = $vars['element'];
  $sub_menu = '';

  if ($element['#below']) {
    $sub_menu = drupal_render($element['#below']);
  }
  $output = '<h3>' . l($element['#title'], $element['#href'], $element['#localized_options']) . '</h3>';

  // Get the node being referenced.
  $href_arr = explode('/', $element['#href']);
  if (count($href_arr) == 2 && $href_arr[0] == "node") {
    $nid = $href_arr[1];
    $node = node_load($nid);

    // Get the summary.
    $summary = '<div class="summary">';
    if (!empty($node->body[LANGUAGE_NONE][0]['safe_summary'])) {
      $summary .= $node->body[LANGUAGE_NONE][0]['safe_summary'];
    }
    elseif (!empty($node->body)) {
      $summary .= text_summary($node->body[LANGUAGE_NONE][0]['safe_value'], $node->body[LANGUAGE_NONE][0]['format'], 300);
    }
    $summary .= '</div>';

      // Get the image.
    $image_markup = '<div class="image">';
    if (!empty($node->field_image)) {
      $image_markup .= theme_image_style([
        'style_name' => 'theme_tile',
        'path' => $node->field_image['und'][0]['uri'],
        'width' => '',
        'height' => '',
        'alt' => $node->field_image['und'][0]['alt'],
        'title' => $node->field_image['und'][0]['title']
      ]);
    }
    else {
      $image_markup .= '<div class="image-placeholder"></div>';
    }
    $image_markup .= '</div>';

    $output = $image_markup . $output;
    $output = $output . $summary;
  }

  return '<li' . drupal_attributes($element['#attributes']) . '>' . $output . $sub_menu . "</li>\n";
}

/**
 * Implements theme_preprocess_views_view_unformatted().
 */
function doeeem_theme_preprocess_views_view_unformatted(&$vars) {
  /** @var view $view */
  $view = $vars['view'];
  if ($view->name == "themes" && $view->current_display == "homepage_block") {
    foreach ($view->result as $index => $row) {
      if (!empty($row->field_field_theme_image)) {
        $vars['classes_array'][$index] .= ' has-thumbnail';
      }
    }
  }
}

function doeeem_theme_preprocess_entity(&$vars) {
  // Add an H3 tag around Definitions for Measures.
  if (!empty($vars['paragraphs_item'])) {
    /** @var ParagraphsItemEntity $item */
    $item = $vars['paragraphs_item'];
    if ($item->field_name === "field_definition") {
      if (!empty($vars['content']['field_title'])) {
        $markup = $vars['content']['field_title'][0]['#markup'];
        $vars['content']['field_title'][0]['#markup'] = '<h3>' . $markup . '</h3>';
      }
    }
  }
}
