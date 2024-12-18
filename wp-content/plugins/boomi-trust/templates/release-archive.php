<section class="release-archive container">
    <?php if ( have_rows( 'release_archive' ) ) : ?>
        <div class="release-archive-container data-container row">
            <?php while ( have_rows( 'release_archive' ) ) : the_row(); ?>
                <div class="header-row col">
                    <h2><?php the_sub_field('section_title'); ?></h2>    
                </div>             
    
                <?php if ( have_rows( 'completed_runtime_releases' ) ) : ?>
                    <div class="completed-release-control-dates-container col col-md-6"">
                        <div class="completed-release-control-dates border-wrap">
                        <?php while ( have_rows( 'completed_runtime_releases' ) ) : the_row(); ?>
                            <div class="sub-head"><h3><?php the_sub_field('title'); ?></h3></div>
                            <?php if ( have_rows( 'completed_release_control_dates' ) ) : ?>
                                <?php while ( have_rows( 'completed_release_control_dates' ) ) : the_row(); ?>
                                    <div class="date"><?php the_sub_field('date'); ?> <?php the_sub_field('details'); ?></div>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ( have_rows( 'completed_platform_releases' ) ) : ?>
                    <div class="completed-release-dates-container col col-md-6">
                        <div class="completed-release-dates border-wrap">
                        <?php while ( have_rows( 'completed_platform_releases' ) ) : the_row(); ?>
                            <div class="sub-head"><h3><?php the_sub_field('title'); ?></h3></div>
                            <?php if ( have_rows( 'completed_release_dates' ) ) : ?>
                                <?php while ( have_rows( 'completed_release_dates' ) ) : the_row(); ?>
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

</section>
