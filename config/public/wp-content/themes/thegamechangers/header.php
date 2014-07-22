<?php
/**
 * The Header for thegamechanger theme
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage thegamechanger
 * @since 1.0
 */
?><?php
/**
 * The Header for our theme
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
?><!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8) ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<link rel='shortcut icon' type='image/x-icon' href='<?php echo get_template_directory_uri(); ?>/images/favicon.ico' />
	<link rel='icon' type='image/x-icon' href='<?php echo get_template_directory_uri(); ?>/images/favicon.ico' />
	<!--[if lt IE 9]>
	<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js"></script>
	<![endif]-->
	<script type="text/javascript">var switchTo5x=true;</script>
	<script src="<?php echo get_template_directory_uri(); ?>/js/jquery-1.10.1.min.js"></script>
	<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/jquery.mousewheel-3.0.6.pack.js"></script>
	<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/jquery.fancybox.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/css/jquery.fancybox.css?v=2.1.5" media="screen">
	<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/disable.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/style.css" media="screen">
	<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/script.js"></script>
	<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/tiny.fader.packed.js"></script>
	<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/crawler.js"></script>
	<!-- Text and/or Image Crawler Script v1.5 (c)2009-2011 John Davenport Scheuer as first seen in http://www.dynamicdrive.com/forums/ username: jscheuer1 - This Notice Must Remain for Legal Use updated: 4/2011 for random order option, more (see below) -->
	
	<script type="text/javascript">
		$(document).ready(function() {
			/*
			 *  Simple image gallery. Uses default settings
			 */

			$('.fancybox').fancybox();

			/*
			 *  Different effects
			 */

			// Change title type, overlay closing speed
			$(".fancybox-effects-a").fancybox({
				helpers: {
					title : {
						type : 'outside'
					},
					overlay : {
						speedOut : 0
					}
				}
			});

			// Disable opening and closing animations, change title type
			$(".fancybox-effects-b").fancybox({
				openEffect  : 'none',
				closeEffect	: 'none',

				helpers : {
					title : {
						type : 'over'
					}
				}
			});

			// Set custom style, close if clicked, change title type and overlay color
			$(".fancybox-effects-c").fancybox({
				wrapCSS    : 'fancybox-custom',
				closeClick : true,

				openEffect : 'none',

				helpers : {
					title : {
						type : 'inside'
					},
					overlay : {
						css : {
							'background' : 'rgba(238,238,238,0.85)'
						}
					}
				}
			});

			// Remove padding, set opening and closing animations, close if clicked and disable overlay
			$(".fancybox-effects-d").fancybox({
				padding: 0,

				openEffect : 'elastic',
				openSpeed  : 150,

				closeEffect : 'elastic',
				closeSpeed  : 150,

				closeClick : true,

				helpers : {
					overlay : null
				}
			});

			/*
			 *  Open manually
			 */

			
			$("#fancybox-manual-b").click(function() {
				$.fancybox.open({
					href : 'iframe.html',
					type : 'iframe',
					padding : 5
				});
			});

			


		});
	</script>
	<?php wp_head(); ?>
	</head>
<body <?php body_class(); ?>>
<!--[if lte IE 7]>
    
       <div id="ie-message" style="border-bottom:2px solid #000000; background:#fff;">

	You seem to be accessing our site using Internet Explorer 7 or older. We strongly recommend that you upgrade to a more modern browser.
    
       </div>
    
<![endif]-->
	<div class="container">
		<header>
    	<div class="headerinner">
    		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/gamechangers-logo.png" alt="GameChangers" id="logo"></a>
            	<?php wp_nav_menu( array( 'theme_location' => 'header-menu', 'container_class' => 'nav' ) ); ?>
            
        </div>
    </header>