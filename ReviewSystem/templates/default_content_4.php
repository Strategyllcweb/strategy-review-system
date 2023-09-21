<?php
if ( !isset($args['form_id']) ) {
    return;
}
$feedback_form_id = $args['form_id'];
?>

<div class="content--container">
    <h3>Feedback</h3>
    <p>We are sorry to hear that you didn't have the greatest experience. We ask that you provide some feedback about your experience so that we can improve our services in the future.</p>
    <div class="content__feedback--container">
        <?php echo do_shortcode('[gravityform id="' . $feedback_form_id . '" title="false" ajax="true"]'); ?>
    </div>
</div>
