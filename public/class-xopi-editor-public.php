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

class Xopi_Editor_Public {

	private $plugin_name;

	private $version;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function editable_title($html) {
		global $post;
		
		if ( !is_admin() && in_the_loop() && is_user_logged_in() && current_user_can('edit_posts') ) {
			$html = "<span class='xopi' id='title_{$post->ID}'>".$html."</span>";
		}

		return $html;

	}

	public function editable_content($html) {
		global $post;

		if ( !is_admin() && in_the_loop() && is_user_logged_in() && current_user_can('edit_posts') ) {
		//if ( !is_admin() && (in_the_loop() || (is_page() && is_main_query())) && is_user_logged_in() && current_user_can('edit_posts') ) {
			$html = <<<END
				<div id='xopi_content_{$post->ID}' class='xopi_content_preview'>$html</div>
END;
			
			$settings = array( 'drag_drop_upload' => true );
		
			echo "<div class='xopi_content_edit' style='display:none'>";
			echo "<div class='xopi_message' id='xopi_message_{$post->ID}'></div>";

			wp_editor( $html, 'xopi_'.$post->ID, $settings );

			echo "</div>";
		}
		return $html;

	}

	public function xopi_tinymce_before_init($args) {
		if (!is_admin()) {
		}
		return $args;
	}
 
	public function xopi_tinymce_buttons_2($buttons) {
		if (!is_admin()) {
			array_unshift( $buttons, 'styleselect' );
		}
		return $buttons;
	}
 

	public function xopi_tinymce_buttons_4($buttons) {
		if (!is_admin()) {
			array_push($buttons, 'cancel', 'save', 'xopi');
		}
		return $buttons;
	}
 

	public function xopi_tinymce_javascript($plugin_array) {
		if (!is_admin()) {
			$plugin_array['xopi'] = plugins_url('../tinymce/',__FILE__ ) . 'xopi/plugin.js';
		}

		return $plugin_array;
	}

	public function xopi_admin_bar( $wp_admin_bar ) {

		if (!is_admin()) {
			$args = array(
				'id'    => 'xopi_admin',
				'title' => '<span class="xopi-icon"></span><span class="xopi-name">XOpi&trade;</span>',
				'href'  => '#',
				'meta'  => array( 'onclick' => 'toggleXopi(); return false;','class' => 'xopi-toolbar' )
			);
			$wp_admin_bar->add_node( $args );

		}

	}
	
	public function enqueue_styles() {

		if ( !is_admin() && is_user_logged_in() && current_user_can('edit_posts') ) {
			wp_enqueue_style( 'xopi_editor_public', plugin_dir_url( __FILE__ ) . 'css/xopi-editor-public.css', array(), $this->version, 'all' );
		}

	}

	public function enqueue_scripts() {

		if ( is_user_logged_in() && current_user_can('edit_posts') ) {
			wp_enqueue_script( 'xopi_editor_public', plugin_dir_url( __FILE__ ) . 'js/xopi-editor-public.js', array( 'jquery' ), $this->version, false );

			$xopi_nonce = wp_create_nonce( 'xopi_editor' );
			$plugin_dir = plugin_dir_url( __FILE__ );

			wp_localize_script( 'xopi_editor_public', 'xopi_data', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'user_id' => get_current_user_id(),
				'nonce'    => $xopi_nonce,
				'xopi_dir' => $plugin_dir
			) );

			wp_enqueue_media();

		}
	}
}
