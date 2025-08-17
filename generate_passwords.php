<?php
// Generate password hash for Akhil's password
$username = 'Akhil';
$password = '2012';

$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password hash for Akhil:\n\n";
echo "Username: $username\n";
echo "Password: $password\n";
echo "Hash: $hash\n";
echo "SQL: UPDATE users SET password = '$hash' WHERE username = '$username';\n";
echo "\n";
?> 