<?php
if ( !isset($args['locations'] ) ) {
    return;
}
$locations = $args['locations'];
?>

<div class="content--container">
    <h3>Select Location</h3>
    <p>We love serving you, and would love to get some feedback on how we can improve.</p>
    <p>Please select the location you have received service at, and let us know how we did.</p>
    <div class="content__location">
        <label class="sr-only" for="content__location--select">Select your location:</label>
        <select id="content__location--select">
            <option value="" disabled selected>Select Location</option>
            <?php 
                foreach ( $locations as $location ) {
                    if ( isset($location['slug']) && isset($location['name']) ) {
                        ?>
                        <option value="<?php echo esc_attr($location['slug']); ?>"><?php echo esc_html($location['name']); ?></option>
                        <?php
                    }
                }
            ?>
        </select>
    </div>
</div>
