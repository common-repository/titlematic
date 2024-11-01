<?php
/*
Plugin Name: Titlematic
Plugin URI: http://familypress.net/titlematic/
Description: Set the post title for untitled posts based on the first characters of a post or a default title string.
Author: Isaac Wedin
Version: 0.2
Author URI: http://familypress.net/

*/

/* Copyright 2010 Isaac Wedin (email : isaac@familypress.net)
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. 
http://www.opensource.org/licenses/gpl-license.php

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
*/

$titlematic_plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain( 'titlematic', 'wp-content/plugins/' . $titlematic_plugin_dir, $titlematic_plugin_dir );

function titlematic_options_init() {
	register_setting('titlematicoptions_options','titlematic_options','titlematic_sanitize');
}

add_action('admin_init','titlematic_options_init');

function titlematic_sanitize($input) {
	$output = array();
	$output['deftitle'] = wp_filter_nohtml_kses($input['deftitle']);
	$output['titlesize'] = (int)$input['titlesize'];
	$output['aftertitle'] = wp_filter_nohtml_kses($input['aftertitle']);
	return $output;
}

// only users with "manage_options" permission should see the Options page
function titlematic_add_options_page() {
	add_options_page('Titlematic', 'Titlematic', 'manage_options', 'titlematic_options', 'titlematic_options_subpanel');
}

add_action('admin_menu', 'titlematic_add_options_page');

// generates the Titlematic Options subpanel
function titlematic_options_subpanel() {
	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	echo "<div class='wrap'>\n<h2>" . __('Titlematic options','titlematic') . "</h2>\n<form method='post' action='options.php'>\n";
	settings_fields('titlematicoptions_options');
	$titlematic_options = get_option('titlematic_options');
	$titlematic_options = wp_parse_args((array)$titlematic_options, array('deftitle'=>'Untitled Post', 'titlesize'=>25, 'aftertitle'=>'&#8230;'));
	echo "<fieldset class='options'>\n<table class='form-table'>\n<tbody>\n";
	echo "<tr>\n<th scope='row'>" . __('Default title text:','titlematic') . "</th>\n" . "<td><input name='titlematic_options[deftitle]' type='text' value='" . $titlematic_options['deftitle'] . "' size='30'><br />" . __('Enter the string you would like to use as a default if a post contains no content, only an image or video.','titlematic') . "</td>\n</tr>\n";
	echo "<tr>\n<th scope='row'>" . __('Title size:','titlematic') . "</th>\n" . "<td><input name='titlematic_options[titlesize]' type='text' value='" . $titlematic_options['titlesize'] . "' size='10'><br />" . __('If the post contains usable content, this many characters will be used to construct a title.','titlematic') . "</td>\n</tr>\n";
	echo "<tr>\n<th scope='row'>" . __('After-title text:','titlematic') . "</th>\n" . "<td><input name='titlematic_options[aftertitle]' type='text' value='" . htmlentities(wp_kses_stripslashes($titlematic_options['aftertitle'])) . "' size='20'> " . __('Looks like this: ','titlematic') . '<strong>' . $titlematic_options['aftertitle'] . "</strong><br />" . __('Text or character to place after the constructed title. The default is the HTML entity for an ellipsis (3 dots), <strong>&amp;#8230;</strong>.','titlematic') . "</td>\n</tr>\n";
	echo "\n</tbody>\n</table>\n</fieldset>\n<p class='submit'><input type='submit' name='Submit' value='" . __('Update Options &raquo;','titlematic') . "' /></p>\n</form>\n</div>\n";
}

function titlematic_doit($post_title) {
	$titlematic_options = get_option('titlematic_options');
	$titlematic_options = wp_parse_args((array)$titlematic_options, array('deftitle'=>'Untitled Post', 'titlesize'=>25, 'aftertitle'=>'&#8230;'));
	if( !empty($post_title) ) {
		return $post_title;
	} else {
		if (!empty($_REQUEST['content'])) {
			$post_content = $_REQUEST['content'];
			$post_title = strip_tags( $post_content );
			if( strlen( $post_title ) > $titlematic_options['titlesize']) {
				$post_title = trim( substr( $post_title, 0, $titlematic_options['titlesize']) ) . $titlematic_options['aftertitle'];
			} elseif( strlen( $post_title ) == 0) {
				$post_title = $titlematic_options['deftitle'];
			}
		} else {
			$post_title = $titlematic_options['deftitle'];
		}
		return $post_title;
	}
}

add_filter('title_save_pre', 'titlematic_doit');

?>
