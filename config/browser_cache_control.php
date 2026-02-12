<?php
// Prevent back-button access after logout
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0, post-check=0, pre-check=0");
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
