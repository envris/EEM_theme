<?php
/**
 * @file
 * Contains the theme's functions to manipulate Drupal's default markup.
 *
 * Complete documentation for this file is available online.
 * @see https://drupal.org/node/1728096
 */

/**
 * Implements hook_media_wysiwyg_format_form_prepare_alter().
 */
function doeeem_admin_media_wysiwyg_format_form_prepare_alter(&$form, &$form_state, $file) {
  // Show the caption field.
  $form['options']['fields']['field_caption']['#access'] = TRUE;
}
