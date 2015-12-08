<?php
$title = $args['post_title'];
$action = $args['action'];
?>

Please review content on the DCLG Intranet.

The page: <?php echo $title; ?>, has been scheduled for review, please review the content of this post at the earliest opportunity.

<?php if ('email' !== $action): ?>
    The page status has been set to <?php echo $action; ?>, until it can be reviewed.
<?php endif; ?>

Thank you
