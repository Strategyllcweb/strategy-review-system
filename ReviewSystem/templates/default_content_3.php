<?php
if ( !isset($args['vendors']) ) {
    return;
}
$vendors = $args['vendors'];
?>

<div class="content--container">
    <h3>Leave a Review</h3>
    <p>We are glad that you had a good experience with our service! We would like to ask that you leave a review on any of the third-party review systems listed below.</p>
    <p>If there are no links available, please reach out and let us know via our <a href="/contact/">Contact Form</a>.</p>
    <div class="content__review-vendors">
        <?php
        foreach ( $vendors as $vendor ) {
            if ( isset( $vendor['name'] ) && isset( $vendor['id'] ) && isset( $vendor['icon-class'] ) ) {
                ?>
                <div id="<?php echo esc_attr( $vendor['id'] ); ?>" class="review-vendor">
                    <a class="review-vendor__link" href="" target="_blank" rel="noopener" data-vendor="<?php echo esc_attr( $vendor['name'] ); ?>">
                        <span class="fa-layers fa-fw review-vendor__icon">
                            <i class="fas fa-circle"></i>
                            <i class="fab <?php echo esc_attr( $vendor['icon-class'] ); ?>" aria-hidden="true"></i>
                        </span>
                    </a>
                </div>
                <?php
            }
        }
        ?>
    </div>
</div>