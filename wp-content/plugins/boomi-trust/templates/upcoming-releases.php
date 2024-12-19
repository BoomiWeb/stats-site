<section class="boomi-stats-upcoming-releases container">
    <?php if ( have_rows( 'upcoming_releases' ) ) : ?>
        <div class="upcoming-releases-container data-container row">
            <?php while ( have_rows( 'upcoming_releases' ) ) : the_row(); ?>
                <div class="header-row col">
                    <h2><?php the_sub_field('section_title'); ?></h2>    
                </div>             
    
                <?php if ( have_rows( 'upcoming_release_dates' ) ) : ?>
                    <div class="upcoming-release-control-dates col col-md-6">
                        <div class="release-control-dates border-wrap">
                        <?php while ( have_rows( 'upcoming_release_dates' ) ) : the_row(); ?>
                            <div class="sub-head"><h3><?php the_sub_field('title'); ?></h3></div>
                            <?php if ( have_rows( 'upcoming_release_control_dates' ) ) : ?>
                                <?php while ( have_rows( 'upcoming_release_control_dates' ) ) : the_row(); ?>
                                    <div class="date"><?php the_sub_field('date'); ?> <?php the_sub_field('details'); ?></div>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ( have_rows( 'upcoming_platform_release_dates' ) ) : ?>
                    <div class="upcoming-platform-release-control-dates col col-md-6">
                        <div class="platform-release-control-dates border-wrap">
                        <?php while ( have_rows( 'upcoming_platform_release_dates' ) ) : the_row(); ?>
                            <div class="sub-head"><h3><?php the_sub_field('title'); ?></h3></div>
                            <?php if ( have_rows( 'upcoming_release_dates' ) ) : ?>
                                <?php while ( have_rows( 'upcoming_release_dates' ) ) : the_row(); ?>
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
