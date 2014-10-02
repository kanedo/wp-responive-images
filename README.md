wp-responive-images
===================

Use the picture element for responsive images in wordpress

## usage

just upload the plugin and install it. It'll take of the rest

## customization

use the filter `kanedo_responsive_image_scales` to add or remove scales.

e.g.
```php
function custom_scales( $scales ){
	return array(2,3,4);
}
add_filter( 'kanedo_responsive_image_scales', 'custom_scales');
```
to generate thumbnails for `1x`, `2x`, `3x` and `4x`. Default is `1x`, `2x`, `3x`

## output
the plugin replaces the img tags and adds the attr `srcset`

## shortcode

the plugin provides a shortcode `responsive_image` which has 4 attributes:
``` php
'id'    => 1, // the attachment id
'size' 	=> 'full', // the image size
'alt'	=> '', // the alt text
'align' => 'none' // the alignment
```	

use it like this:
```
[responsive_image id='490' size='thumbnail' alt='test' align='none']
```

it'll be inserted when using the wordpress media picker

## changelog
### Version 1.0.1 (2014-09-30)
- fix JS Asset Path

### Version 1.0 ( 2014-09-30 )
- initial release
