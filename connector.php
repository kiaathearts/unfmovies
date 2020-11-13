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
// $db=new DB\SQL(
//     'mysql:host=localhost;port=3306;dbname=UNFMovies',
//     'root',
//     'Ng110281'
// );
$db=new DB\SQL(
    'mysql:host=ls-2004cb36f96623ccb50749eb4f1dee51ac36a24e.cp56ir4pojm7.us-east-1.rds.amazonaws.com;port=3306;dbname=UNFMovies',
    'dbmasteruser',
    'b}izs7BRk2S_e7{^v1%sy1K[+?BWIat]'
);
?>
