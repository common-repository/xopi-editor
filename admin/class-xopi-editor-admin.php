<?php

/*
    Copyright (C) 2015 WildFireWeb, Inc

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

	Please note that this license contains additional terms according to 
	section 7 of GPL Version 3. Before making any modifications or redistributing 
	this code please review the license additional terms.

    You should have received a copy of the GNU General Public License and the Additional Terms
    along with this program.  If not, see http://wildfireweb.com//xopi-gpl3-license.html

	This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

*/

class Xopi_Editor_Admin {

	private $plugin_name;

	private $version;
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function xopi_ajax_handler() {

		if ( is_user_logged_in() && current_user_can('edit_posts') ) {

			check_ajax_referer( 'xopi_editor', '_ajax_nonce', true );

			$item_id = isset($_POST['item_id']) ? sanitize_text_field($_POST['item_id']) : null;
			$item_type = isset($_POST['item_type']) ? sanitize_text_field($_POST['item_type']) : null;
			$item_value = isset($_POST['item_value']) ? $_POST['item_value'] : null;

			if ($item_id && $item_type) {

				if ($item_type == 'title') {
					$my_post = array(
					  'ID'           => $item_id,
					  'post_title'   => $item_value
					 );
					wp_update_post( $my_post );
					wp_die('success');
				}
				else if ($item_type == 'content') {
					$my_post = array(
					  'ID'           => $item_id,
					  'post_content' => $item_value
					 );
					wp_update_post( $my_post );
					wp_die('success');
				}
				else if ($item_type == 'revision_list') {
							
					$revisions = wp_get_post_revisions($item_id, array('posts_per_page' => 20));

					$html = "<strong>Click to return to a previous version:</strong><br>";

					if (is_array($revisions)) {
						foreach($revisions as $revision) {
							$html .= "<a href='javascript:void(0)' onclick='doRevert({$revision->ID}); return false;'>{$revision->post_modified} - {$revision->post_title}</a><br>";
						}
					}

					//error_log('revs:'.$html);
					
					wp_die($html);
				}
				else if ($item_type == 'get_revision') {
							
					$post = get_post($item_id);

					$html = "";

					if ($post)
						$html = $post->post_content;

					//error_log('getrev:'.$html);
					
					wp_die($html);
				}


			}

			//error_log('admin:'.print_r($_POST, true));
			//wp_die(print_r($_POST, true));
		}

		die('error');

	}	

	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/xopi-editor-admin.css', array(), $this->version, 'all' );

	}

	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/xopi-editor-admin.js', array( 'jquery' ), $this->version, false );

		//wp_enqueue_media();

	}

}
