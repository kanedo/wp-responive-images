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
		/**
		 * constructor
		 * @uses add_action
		 * @uses add_filter
		 * @since 1.0
		 **/
		function __construct()
		{
			add_action( 'wp_enqueue_scripts', array($this, 'register_javascript') );
			add_filter('image_send_to_editor', array($this, 'send_to_editor'), 10, 9);
			add_filter('post_thumbnail_html', array($this, 'filter_post_thumbnail'), 10, 9);
			add_action('init', array($this, 'register_image_sizes'), 100);

			$this->register_github_updater();
			$this->register_shortcode();
		}

		/**
		 * returns the scale factors
		 * filter with 'kanedo_responsive_image_scales'
		 * @uses apply_filters 
		 * @return array
		 **/
		public function get_scale_factors(){
			/**
			 * Filter the used scale factors
			 * @param array $scales The scale factors
			 */
			return apply_filters( 'kanedo_responsive_image_scales', array(2, 3) );
		}

		/**
		 * register the github update class
		 * @see https://github.com/bfintal/Plugin-Update
		 **/
		public function register_github_updater(){
			if ( is_admin() ) {
				new BFIGitHubPluginUpdater( __FILE__, 'kanedo', "wp-responive-images" );
			}
		}

		/**
		 * register the javascript files
		 * @uses wp_register_script
		 * @uses wp_enqueue_script
		 * @uses plugin_dir_url
		 **/ 
		public function register_javascript()
		{
			wp_register_script( 'picturefill', plugin_dir_url(__DIR__)."/assets/js/picturefill.min.js");
			wp_enqueue_script('picturefill' );
		}

		/**
		 * register all scaled image sizes.
		 * only for imagesizes that doesn't match the pattern /\-\d+x/ (e.g. foo-2x)
		 * @see self::get_scale_factors
		 * @uses get_intermediate_image_sizes()
		 **/ 
		public function register_image_sizes()
		{
			foreach (get_intermediate_image_sizes() as $size) {
				if( !preg_match("/\-\d+x/", $size)){
					foreach ($this->get_scale_factors() as $scale) {
						$this->register_scaled_image_size($size, $scale);
					}
				}
			}
		}

		/**
		 * returns properties of image sizes. if no attribute is given all image sizes are returned
		 * @param string $size 
		 * @uses get_intermediate_image_sizes
		 * @uses get_option
		 * @return array
		 **/
		protected function get_image_sizes( $size = '' ) {

	        global $_wp_additional_image_sizes;

	        $sizes = array();
	        $get_intermediate_image_sizes = get_intermediate_image_sizes();

	        // Create the full array with sizes and crop info
	        foreach( $get_intermediate_image_sizes as $_size ) {

	                if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {

	                        $sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
	                        $sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
	                        $sizes[ $_size ]['crop'] = (bool) get_option( $_size . '_crop' );

	                } elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

	                        $sizes[ $_size ] = array( 
	                                'width' => $_wp_additional_image_sizes[ $_size ]['width'],
	                                'height' => $_wp_additional_image_sizes[ $_size ]['height'],
	                                'crop' =>  $_wp_additional_image_sizes[ $_size ]['crop']
	                        );

	                }

	        }

	        // Get only 1 size if found
	        if ( $size ) {

	                if( isset( $sizes[ $size ] ) ) {
	                        return $sizes[ $size ];
	                } else {
	                        return false;
	                }

	        }

	        return $sizes;
		}

		/**
		 * register the scaled image sizes
		 * @uses add_image_size
		 * @param string $size
		 * @param int $scale
		 **/
		protected function register_scaled_image_size( $size, $scale ){
			$image_size = $this->get_image_sizes( $size );
			if( array_key_exists('width', $image_size) 
				&& array_key_exists('height', $image_size)
				&& array_key_exists('crop', $image_size)){
				add_image_size( $size."-{$scale}x", $image_size['width'] * $scale, $image_size['height'] * $scale, $image_size['crop'] );
			}
		}

		/**
		 * register shortcode 'responsive_image'
		 * @uses add_shortcode
		 **/ 
		public function register_shortcode()
		{
			add_shortcode( 'responsive_image', array($this, 'do_shortcode') );
		}

		/**
		 * filter the send_to_editor action
		 * @return string
		 **/ 
		public function send_to_editor( $html, $id, $caption, $title, $align, $url, $size, $alt )
		{
			return "[responsive_image id='{$id}' size='{$size}' alt='{$alt}' align='{$align}']";
		}

		/**
		 * parse the shortcode 'responsive_image'
		 * @param array $attr
		 * @uses shortcode_atts
		 * @return string
		 **/ 
		public function do_shortcode( $attr )
		{
			extract( shortcode_atts( array(
				'id'    => 1,
       			// You can add more sizes for your shortcodes here
				'size' 	=> 'full',
				'alt'	=> '',
				'align' => 'none'	
			), $attr ) );
			return $this->get_img_tag( $id, $alt, $size, $align );
		}

		/**
		 * filter the post_Thumbnail 
		 * @param string $html
		 * @param int $post_id
		 * @param int $post_thumbnail_id
		 * @param string $size
		 * @param string $attr
		 * @return string
		 **/ 
		public function filter_post_thumbnail( $html, $post_id, $post_thumbnail_id, $size, $attr ){
			if( empty($html) ){
				return $html;
			}
			return $this->get_img_tag( $post_thumbnail_id, '', $size);
		}

		/**
		 * return an image tag with src set
		 * @param int $id
		 * @param string $alt
		 * @param string $size
		 * @param string $align (default '')
		 * @return string
		 **/ 
		public function get_img_tag( $id, $alt, $size, $align = '')
		{
			$srcset = $this->get_image_src_set( $id, $size );
			$align = (empty($align))?'':"align{$align}";
			$srcset_string = $this->get_src_set_string( $this->get_image_src_set( $id, $size ) );
			return "<img src='{$srcset['1x']}' alt='{$alt}' srcset='{$srcset_string}' class='{$align} size-{$size} {$size} wp-image-{$id}' />";
		}

		/**
		 * transform an array of hidpi images into an html srcset string
		 * @param array $srcset
		 * @return string
		 **/ 
		protected function get_src_set_string( $srcset ){
			$srcset_string = array();
			foreach ($srcset as $key => $value) {
				$srcset_string[] = "{$value} {$key}";	
			}
			return implode(', ', $srcset_string);
		}

		/**
		 * returns an array with all scaled images 
		 * @param int $image the image id
		 * @param strin $size
		 * @uses wp_get_attachment_image_src
		 * @return array an array [1x => '...', 2x => '...', ...]
		 **/ 
		protected function get_image_src_set( $image, $size )
		{		
			$set = array();
			$sizes = array( "1x" => $size);
			foreach ($this->get_scale_factors() as $scale) {
				$sizes[$scale."x"] = $size."-{$scale}x";
			}
			foreach ($sizes as $scale => $size) {
				$wp_image = wp_get_attachment_image_src( $image, $size );
				$set[$scale] = $wp_image[0];
			}
			return $set;		
		}
	}
	
	$kanedo_wp_responsive_images = new Kanedo_WP_Responsive_Images();
}