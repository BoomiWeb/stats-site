			<footer>	
    			<div class="container footer-menu">
                    <div class="row">
                        <div class="col-xs-12">
                        	<?php
                            wp_nav_menu(array(
                    		    'theme_location' => 'footer',
                                'container' => false,
                            ));
                            ?>
                            <i class="fab fa-twitter"></i>
                        </div> 			
                    </div>
    			</div>
    						
				<div class="copyright">
					&copy; <?php echo date('Y'); ?> Boomi, Inc.. All rights reserved.
				</div>
			</footer>
	
			<?php wp_footer(); ?>
		
		</div><!-- .wrapper -->
	</body>
</html>