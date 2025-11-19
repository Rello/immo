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
