 <?php
// $servername = "localhost";
// $username = "root";
// $password = "Ng110281";
// $dbname = "UNFMovies";

// // Create connection
// $conn = new mysqli($servername, $username, $password, $dbname);

// // Check connection
// if ($conn->connect_error) {
//   die("Connection failed: " . $conn->connect_error);
// }
// echo "Connected successfully";
require '/home/kia/vendor/autoload.php';
$db=new DB\SQL(
    'mysql:host=localhost;port=3306;dbname=UNFMovies',
    'root',
    'Ng110281'
);
?> 