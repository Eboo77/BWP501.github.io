<?php
$host = 'aws-0-eu-north-1.pooler.supabase.com';
$dbname = 'postgres';
$username = 'postgres.rtcmhbxpnufxbhlohmgo';
$password = '1beMXFOoYFUjGgwy';
$port = '5432'; 

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>