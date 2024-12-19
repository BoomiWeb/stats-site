<div class="boomi-stats-notifications">
    <?php include_once dirname( __FILE__ ) . '/upcoming-releases.php'; ?>

    <?php 
    if (boomi_trust_has_infrastructure_section()) {
        include_once dirname( __FILE__ ) . '/infrastructure-releases.php';
    }
    ?>

    <?php include_once dirname( __FILE__ ) . '/release-archive.php'; ?>
</div>
