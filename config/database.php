<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'PosadaDelMar');
define('DB_USER', 'root');
define('DB_PASS', '');

class Database {
    private $conn;

    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=".DB_HOST.";dbname=".DB_NAME, 
                DB_USER, 
                DB_PASS
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("SET NAMES utf8");
        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}

// Crear instancia de la base de datos
$database = new Database();
$conn = $database->getConnection();
?>
<?php
// Configuración de la base de datos para PosadaDelMar
$host = 'localhost';      // Servidor de la base de datos
$dbname = 'PosadaDelMar'; // Nombre de la base de datos
$username = 'root';       // Usuario de la base de datos
$password = '';           // Contraseña del usuario

try {
    // Crear conexión PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Configurar atributos de PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Configurar zona horaria si es necesario
    $pdo->exec("SET time_zone = '-05:00'"); // Ejemplo para zona horaria de Perú
    
} catch (PDOException $e) {
    // Manejar errores de conexión
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Función para sanitizar entradas
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>