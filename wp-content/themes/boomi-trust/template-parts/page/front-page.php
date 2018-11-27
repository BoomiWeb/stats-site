<div class="container">
	<div class="row">
		<div class="col-md-7">
			<div class="row">
				<div class="col-xs-12">
					<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
						<?php the_content(); ?>
					<?php endwhile; endif; ?>
				</div>
			</div>
		</div>
		
		<div class="col-md-5">
			<?php echo do_shortcode('[pickle_calendar]'); ?>
			
			<div class="trust-pc-key clearfix">
    			<ul class="key-list clearfix">
        			<li class="key rd">Release Date</li>
                    <li class="key rcd">Release Control Date</li>
    			</ul>
			</div>
		</div>
	</div>
</div><!-- .container -->