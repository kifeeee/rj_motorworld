<?php
// Run this file in your browser to get the hashed password
$password = 'admin1234';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo "Your hash for admin1234 is: <br><strong>" . $hashed_password . "</strong>";
?>