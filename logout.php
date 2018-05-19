<?php
setcookie("login", '',time() -1);
setcookie("id_user", '', time() -1);
header("Location: registration.php");