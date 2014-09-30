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
			add_action( 'wp_enqueue_scripts', array($this, 'register_javascript') );
			$this->register_github_updater();
		}

		public function register_github_updater(){
			if ( is_admin() ) {
				new BFIGitHubPluginUpdater( __FILE__, 'kanedo', "wp-responive-images" );
			}
		}

		protected function get_image_sizes(){
			return array(
				'large-img-2x'	=> 2048,
				'medium-img-2x'	=> 600,
				'small-img-2x'	=> 300
				);
		}

		public function register_javascript()
		{
			wp_register_script( 'picturefill', plugin_dir_url(__DIR__)."/assets/js/picturefill.min.js");
			wp_enqueue_script('picturefill' );
		}

		public function register_image_sizes()
		{
			foreach ($this->get_image_sizes() as $img_name => $size) {
				add_image_size( $img_name, $size );
			}
		}

		public function register_shortcode()
		{
			# code...
		}

		public function do_shortcode( $attr )
		{
			extract( shortcode_atts( array(
				'imageid'    => 1,
        // You can add more sizes for your shortcodes here
				'size1' => 0,
				'size2' => 600,
				'size3' => 1000,
				), $atts ) );

			$mappings = array(
				$size1 => 'small-img',
				$size2 => 'medium-img',
				$size3 => 'large-img'
				);

			return
			'<picture>
			<!--[if IE 9]><video style="display: none;"><![endif]-->'
			. tevkori_get_picture_srcs( $imageid, $mappings ) .
			'<!--[if IE 9]></video><![endif]-->
			<img srcset="' . wp_get_attachment_image_src( $imageid[0] ) . '" alt="' . tevkori_get_img_alt( $imageid ) . '">
			<noscript>' . wp_get_attachment_image( $imageid, $mappings[0] ) . ' </noscript>
		</picture>';
	}
}

protected function get_image_alt( $image )
{
	$img_alt = trim( strip_tags( get_post_meta( $image, '_wp_attachment_image_alt', true ) ) );
	return $img_alt;
}

protected function get_image_src_set( $image, $mappings )
{
	$arr = array();
	foreach ( $mappings as $type => $size ) {
		$image_src = wp_get_attachment_image_src( $image, $type );
		$arr[] = '<source srcset="'. $image_src[0] . '" media="(min-width: '. $size .'px)">';
	}
	return implode( array_reverse ( $arr ) );
}
}
$kanedo_wp_responsive_images = new Kanedo_WP_Responsive_Images();
}