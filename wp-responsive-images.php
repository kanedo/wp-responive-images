<?php
/*
Plugin Name: WP Responsive Image
Description: use the picture element for responsive images
Plugin URI: http://blog.kanedo.net
Author: Gabriel Bretschner
Author URI: http://blog.kanedo.net
Version: 1.0
License: GPL2
Text Domain: kanedo_wp_responsive_images
Domain Path: /
*/

/*

    Copyright (C) 2014  Gabriel Bretschner  software@kanedo.net

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
require_once( 'BFIGitHubPluginUploader.php' );

if ( !class_exists( "Kanedo_WP_Responsive_Images" )) {
	/**
	* Kanedo_WP_Responsive_Images
	*/
	class Kanedo_WP_Responsive_Images
	{
		
		function __construct(argument)
		{
			# code...
		}

		public static function register_github_updater(){
			if ( is_admin() ) {
   				new BFIGitHubPluginUpdater( __FILE__, 'kanedo', "wp-responive-images" );
			}
		}
	}

	Kanedo_WP_Responsive_Images::register_github_updater();
}