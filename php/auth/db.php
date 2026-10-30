<?php
$host = 'aws-0-eu-north-1.pooler.supabase.com';
$dbname = 'postgres';
$username = 'postgres.rtcmhbxpnufxbhlohmgo';
$password = 'oldiXzpbdx1yr8jh';
$port = '6543'; 

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>