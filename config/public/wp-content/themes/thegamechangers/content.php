<?php
/**
 * The default template for displaying content
 *
 * @package WordPress
 * @subpackage theGameChangers
 * @since theGameChangers 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry-content">
		<header class="entry-header">
		<?php the_post_thumbnail(); ?>
			<h2 class="entry-title">
				<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
			</h2>
		</header>
		<?php if(is_single()) : ?>
		<div class="entry-content">
			<?php the_content(); ?>
			<?php wp_link_pages(); ?>
			<?php posts_nav_link( ":", "<", ">" ); ?> 
			<?php comment_form(); ?>
		</div>		
		<?php else : ?>
		<div class="entry-summary">
		<?php the_excerpt(); ?>
		<div>
			<p><a href="<?php echo get_permalink(); ?>"> Read More...</a></p>
		</div>
		</div><!-- .entry-summary -->
		<?php endif ?>
	</div>
</article><!-- #post-<?php the_ID(); ?> -->
