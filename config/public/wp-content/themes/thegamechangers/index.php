<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme and one
 * of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query,
 * e.g., it puts together the home page when no home.php file exists.
 *
 * @link http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */

get_header(); ?>

<div id="main-content" class="main-content">
	<?php
	if ( is_front_page() || is_home() ) {
		// Include the featured content template.
		get_template_part( 'featured-content' );
	} else if ( have_posts() ) { ?>
		<div class="banner life-skills-suite">
			<div class="bannerinner">
				<img src="<?php echo get_template_directory_uri(); ?>/images/banner-life-skills-suite.jpg"/>
        		<h1><strong>Blog</strong></h1>
    		</div>
	</div>
	<?php while ( have_posts() ) { 
				the_post();
					get_template_part( 'content', get_post_format() );
				}
	}
?>
</div><!-- #main-content -->
<?php get_footer(); ?>
</body>
</html>