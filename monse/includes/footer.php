<?php
$base = '../';
?>


    <div id="toast-container"></div>
    <script src="<?= $base ?>assets/js/app.js"></script>
    <?php if (isset($extraJS)) foreach ((array)$extraJS as $js) echo "<script src='{$base}{$js}'></script>\n"; ?>
</body>
</html>