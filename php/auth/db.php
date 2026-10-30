<?php
$host = 'aws-0-eu-north-1.pooler.supabase.com';
$port ='6543';
$dbname = 'postgres';
$username = 'postgres.rtcmhbxpnufxbhlohmgo';
$password = '1beMXFoOYFUiGgwy';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

?>
