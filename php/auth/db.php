<?php
$host = 'aws-0-eu-north-1.pooler.supabase.com';      // e.g., from your cloud provider dashboard
$dbname = 'postgres';
$username = 'postgres.rtcmhbxpnufxbhlohmgo';
$password = '1beMXFoOYFUiGgwy';
$port = '6543';                    // Standard PostgreSQL port

try {
    // Notice it starts with 'pgsql:' instead of 'mysql:' for cloud PostgreSQL
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
host=
port=
database=
user=