<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 * @package WordPress
 * @subpackage boomi-trust
 * @since boomi-trust 1.0.0
 */
?>
<?php get_header(); ?>

<div class="container">
	<div class="row">
		<div class="col-md-12">

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<header class="entry-header">
					<h1 class="page-title"><?php _e( 'Not Found', 'boomi-trust' ); ?></h1>
				</header><!-- .entry-header -->

				<div class="entry-content">
					<h2><?php _e( "Still haven't found what you're looking for?", 'boomi-trust' ); ?></h2>
					<p><?php _e( 'It looks like nothing was found at this location. Maybe try a search?', 'boomi-trust' ); ?></p>

					<?php get_search_form(); ?>
				</div><!-- .entry-content -->

			</article><!-- #post-## -->
		</div>
	</div>
</div>

<?php get_footer(); ?>