<?php
/** @var array $_ */
?>
<div id="app-navigation"></div>
<div id="app-content">
    <div class="immo-welcome"><?php p($_['pageTitle']); ?></div>
</div>
<div id="app-sidebar"></div>
<script>
window.ImmoCurrentUserRole = <?php print_unescaped(json_encode($_['currentRole'])); ?>;
</script>
<script src="<?php print_unescaped(\OCP\Util::linkToScript('immo', 'immo-main')); ?>"></script>
<link rel="stylesheet" href="<?php print_unescaped(\OCP\Util::linkTo('immo', 'css/style.css')); ?>">
