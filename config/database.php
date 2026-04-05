<?php
/**
 * CampusNexus — Database Configuration
 * PDO MySQL Connection with error handling
 */

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'campusnexus');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_NAME', 'CampusNexus');
// Dynamically determine the base URL — always resolves to the project root
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
// __DIR__ is /path/to/project/config — go up one level to get project root
$projectRoot = dirname(__DIR__);
$docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
$projectPath = str_replace('\\', '/', $projectRoot);
// Extract the web-accessible path relative to document root
$basePath = str_replace($docRoot, '', $projectPath);
$basePath = rtrim($basePath, '/');
define('SITE_URL', $protocol . '://' . $host . $basePath);
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

/**
 * Get PDO database connection
 */
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In production, log this instead of displaying
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $pdo;
}
?>
