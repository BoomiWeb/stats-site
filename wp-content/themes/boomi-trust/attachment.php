<?php
/**
 * The template for displaying attachments
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
					<?php	the_title( '<h1 class="entry-title">', '</h1>' );	?>

					<div class="entry-meta">
						<?php
						if ( 'post' == get_post_type() )
							boomi_sales_theme_posted_on();

						edit_post_link( __( 'Edit', 'boomi-trust' ), '<span class="edit-link"><span class="glyphicon glyphicon-pencil"></span>', '</span>' );
						?>
					</div><!-- .entry-meta -->
				</header><!-- .entry-header -->

				<div class="entry-content">
					<?php if (wp_attachment_is_image($post->id)) :
						$att_image = wp_get_attachment_image_src( $post->id, "medium");
						?>
						<p class="attachment">
							<a href="<?php echo wp_get_attachment_url($post->id); ?>" title="<?php the_title(); ?>">
							<img src="<?php echo $att_image[0];?>" width="<?php echo $att_image[1];?>" height="<?php echo $att_image[2];?>"  class="attachment-medium" alt="<?php $post->post_excerpt; ?>" />
							</a>
						</p>
					<?php endif; ?>
				</div><!-- .entry-content -->

				<?php the_tags( '<footer class="entry-meta"><span class="tag-links">', '', '</span></footer>' ); ?>
			</article><!-- #post-## -->

		</div>
	</div><!-- .row -->
</div><!-- .container -->

<?php get_footer(); ?>