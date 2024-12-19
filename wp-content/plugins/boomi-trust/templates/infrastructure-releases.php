<section class="infrastructure-releases container">
    <?php if ( have_rows( 'infrastructure_releases' ) ) : ?>
        <div class="infrastructure-releases-container data-container row">
            <?php while ( have_rows( 'infrastructure_releases' ) ) : the_row(); ?>
                <div class="header-row col">
                    <h2><?php the_sub_field('section_title'); ?></h2>    
                </div>             
    
                <?php if ( have_rows( 'releases' ) ) : ?>
                    <div class="release-month-container col col-md-6">
                        <div class="release-month border-wrap">
                        <?php while ( have_rows( 'releases' ) ) : the_row(); ?>
                            <div class="sub-head"><h3><?php the_sub_field('title'); ?></h3></div>
                            <?php if ( have_rows( 'release_month' ) ) : ?>
                                <?php while ( have_rows( 'release_month' ) ) : the_row(); ?>
                                    <div class="date"><?php the_sub_field('date'); ?> <?php the_sub_field('details'); ?></div>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <div class="footer-notice row"><div class="col-12"><p>Boomi reserves the right to adjust these dates as needed.</p></div></div>
</section>         