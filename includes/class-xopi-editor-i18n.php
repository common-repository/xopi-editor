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

class Xopi_Editor_i18n {

	private $domain;

	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			$this->domain,
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

	public function set_domain( $domain ) {
		$this->domain = $domain;
	}

}
