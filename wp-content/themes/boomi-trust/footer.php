			<footer class="">
				<div class="footer-widgets">
					<div class="container">
						<div class="row">
							<div class="col-sm-6">
								<?php dynamic_sidebar('footer-1'); ?>
							</div>
							<div class="col-sm-6">
								<?php dynamic_sidebar('footer-2'); ?>
							</div>
						</div>
					</div> <!-- /container -->
				</div><!-- .footer-widgets -->
				
				<div class="copyright">
					&copy; <?php echo date('Y'); ?> Boomi, Inc.. All rights reserved.
				</div>
			</footer>
	
			<?php wp_footer(); ?>
		
		</div><!-- .wrapper -->
	</body>
</html>