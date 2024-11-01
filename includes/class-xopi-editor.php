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

class Xopi_Editor {

	protected $loader;

	protected $plugin_name;

	protected $version;

	public function __construct() {

		$this->plugin_name = 'xopi-editor';
		$this->version = XOPI_VERSION;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xopi-editor-loader.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xopi-editor-i18n.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-xopi-editor-admin.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-xopi-editor-public.php';

		$this->loader = new Xopi_Editor_Loader();

	}

	private function set_locale() {

		$plugin_i18n = new Xopi_Editor_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	private function define_admin_hooks() {

		$plugin_admin = new Xopi_Editor_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'wp_ajax_xopi', $plugin_admin, 'xopi_ajax_handler' );

	}

	private function define_public_hooks() {

		$plugin_public = new Xopi_Editor_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_bar_menu', $plugin_public, 'xopi_admin_bar', 999 );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'the_title', $plugin_public, 'editable_title' );
		$this->loader->add_action( 'the_content', $plugin_public, 'editable_content' );

		// Init TinyMCE
		$this->loader->add_filter('tiny_mce_before_init', $plugin_public, 'xopi_tinymce_before_init');

		// Load the TinyMCE xopi plugin
		$this->loader->add_filter('mce_external_plugins', $plugin_public, 'xopi_tinymce_javascript');

		// enable style select on 2nd row
		$this->loader->add_filter('mce_buttons_2', $plugin_public, 'xopi_tinymce_buttons_2');

		// add xopi buttons to last row of mce editor
		$this->loader->add_filter('mce_buttons_4', $plugin_public, 'xopi_tinymce_buttons_4');

	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}

}
