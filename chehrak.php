<?php
/**
 * Plugin Name: Chehrak
 * Plugin URI: http://chehrak.com
 * Description: Chehrak is a service for providing avatars
 * Version: 1.0
 * Author: Ali Farmad
 * Author URI: http://farmad.me
 */
if ( !function_exists( 'chehrak' ) ) :

function chehrak($avatar, $id_or_email, $size = '64', $default = '', $alt = false  ) {
	if ( ! get_option('show_avatars') )
		return false;
		
	if ( false === $alt)
		$safe_alt = '';
	else
		$safe_alt = esc_attr( $alt );

	if ( !is_numeric($size) )
		$size = '64';
	
	if ( !in_array($size, array(32, 64, 128, 256)) ) {
		$request_size = $size;
		if ($size < 32)
			$size = 32;
		elseif ($size > 32 && $size < 64)
			$size = 64;
		elseif ($size > 64 && $size < 128)
			$size = 128;
		elseif ($size > 128)
			$size = 256;
	}
	
	if( !isset($request_size) )
		$request_size = $size;

	$email = '';
	if ( is_numeric($id_or_email) ) {
		$id = (int) $id_or_email;
		$user = get_userdata($id);
		if ( $user )
			$email = $user->user_email;
	} elseif ( is_object($id_or_email) ) {
		// No avatar for pingbacks or trackbacks
		$allowed_comment_types = apply_filters( 'get_avatar_comment_types', array( 'comment' ) );
		if ( ! empty( $id_or_email->comment_type ) && ! in_array( $id_or_email->comment_type, (array) $allowed_comment_types ) )
			return false;

		if ( ! empty( $id_or_email->user_id ) ) {
			$id = (int) $id_or_email->user_id;
			$user = get_userdata($id);
			if ( $user )
				$email = $user->user_email;
		}

		if ( ! $email && ! empty( $id_or_email->comment_author_email ) )
			$email = $id_or_email->comment_author_email;
	} else {
		$email = $id_or_email;
	}

	if ( empty($default) ) {
		$avatar_default = get_option('avatar_default');
		if ( !empty($avatar_default) )
			$default = $avatar_default;
	}

	if ( !empty($email) )
		$email_hash = md5( strtolower( trim( $email ) ) );

	if ( is_ssl() ) {
		$host = 'https://rokh.chehrak.com';
	} else {
		$host = 'http://rokh.chehrak.com';
	}

	if ( 'blank' == $default )
		$default = $email ? 'blank' : includes_url( 'images/blank.gif' );
	elseif ( strpos($default, 'gravatar') != false )
		$default = '';

	if ( !empty($email) ) {
		$out = "$host/";
		$out .= $email_hash;
		$out .= '?size='.$size;
		$out .= '&amp;default=' . urlencode( $default );

		$out = str_replace( '&#038;', '&amp;', esc_url( $out ) );
		$avatar = "<img alt='{$safe_alt}' src='{$out}' class='avatar avatar-{$request_size} photo' height='{$request_size}' width='{$request_size}' />";
	} else {
		$avatar = "<img alt='{$safe_alt}' src='{$default}' class='avatar avatar-{$request_size} photo avatar-default' height='{$request_size}' width='{$size}' />";
	}

	return $avatar;
}
endif;

add_filter( 'get_avatar', 'chehrak', 10, 5);
?>