<?php
/***Problem resolution steps**/
//added apps: Fatfree Framework
//Allow All on apache2.conf.save on Directory
//Allow all on apache2 000-default.conf
//Create htaccess file from f3 
//Enable ssl on php.ini - dev and prod and standard

//COMMIT: Add session start
session_start();

// require('connector.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
 // if($_SERVER['REMOTE_ADDR']!='::1'){
 //    require 'vendor/autoload.php';
 // } else {
    require '/home/kia/vendor/autoload.php';
// }  

$f3 = \Base::instance();
$f3->set('DEBUG', 3);

$db=new DB\SQL(
    'mysql:host=localhost;port=3306;dbname=UNFMovies',
    'root',
    'Ng110281'
);

$f3->set('db', $db);
$f3->set('head', 'templates/head.htm');
$f3->set('navbar', 'templates/navbar.htm');
$f3->set('footscripts', 'templates/footscripts.htm'); 
$f3->set('footer', 'templates/footer.htm');
$f3->set('admin', false);
$f3->set('customer', false);

$f3->set('cart', new \Basket());

//TODO: Change e-mail to email
//TODO: Add security to login
$f3->route('POST /login', 
    function($f3){
        $f3->set('admin_login', false);
        $username = $_POST['email'];
        $password = $_POST['password'];
        $email = "email@email.com";
        $user_query = "SELECT * FROM user WHERE email='".$email."' AND password='".$password."'";
        $user = $f3->get('db')->exec($user_query)[0];
        if( !empty($user) ){
            $_SESSION['logged_in'] = true;
            $_SESSION['userid'] = $user['user_id'];
            $_SESSION['balance'] = $user['balance'] == "" ? 0 : $user['balance'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $f3->reroute("/");
        }else{
            $_SESSION['logged_in'] = false;
            $f3->reroute("/login");
        }
    }
);

//TODO: Test admin login
$f3->route('POST /admin/login', 
    function($f3){
        $f3->set('admin_login', true);
        $username = $_POST['username'];
        $password = $_POST['password'];
        $user_query = "SELECT * FROM employee WHERE username='".$username."' AND password='".$password."'";
        $user = $f3->get('db')->exec($user_query)[0];
        if( !empty($user) ){
            $_SESSION['logged_in'] = true;
            $_SESSION['userid'] = $user['user_id'];
            $f3->reroute("/admin");
        }else{
            $_SESSION['logged_in'] = false;
            $f3->reroute("/admin/login");
        }
    }
);

$f3->route('GET /admin/login', 
    function($f3){
        $f3->set('admin_login', true);
        echo \Template::instance()->render('templates/login.htm');
    }
);

$f3->route('GET /login', 
    function($f3){
        $f3->set('page_title', 'Login');
        echo \Template::instance()->render('templates/login.htm');
    }
);

$f3->route('GET /',
    function($f3) {
        if($_SESSION['logged_in']==false){
            $f3->reroute('/login');
        }
        $f3->reroute('/movies');
    }
);

$f3->route('GET /movies',
    function($f3) {
        if($_SESSION['logged_in']==false){
            $f3->reroute('/login');
        }

        //General page values
        $f3->set('customer', true);  
        $f3->set('admin', false);  
    	$f3->set('page_title', 'Movies');

        //Compute total cart cost here
        $items = $f3->get('cart')->find();
        $cart_total = 0;
        foreach($items as $item){
            $cart_total += $item->amount;
        }
        $f3->set('cart_total_cost', $cart_total);

        //Query genres here
        $f3->set('genres',$f3->get('db')->exec('SELECT * FROM genre'));

        //Initialize movies
        $movies_query ='SELECT * FROM movie JOIN genre ON movie.genre_id=genre.genre_id JOIN director ON movie.director_id = director.director_id';
        $f3->set('movies', $f3->get('db')->exec($movies_query));

        //Display the page
    	$f3->set('content', 'templates/movies_list.htm');  
		echo \Template::instance()->render('templates/master.htm');
    }
);

//TODO: Add safety to password creation
//TODO: Add update success notification
$f3->route('POST /profile/@userid/update', 
    function($f3){
        $password = $_POST['password'];
        $userid = $f3->get('PARAMS.userid');
        $update_stmt = "UPDATE user SET password='".$password."' WHERE user_id=".$userid;
        $f3->get('db')->exec($update_stmt);
        $route = "/profile/".$f3->get('PARAMS.userid');

        $f3->reroute($route);
    }
);

$f3->route('POST /movies/query', 
    function($f3){
        if($_SESSION['logged_in']==false){
            $f3->reroute('/login');
        }

        //Compute total cart cost here
        $items = $f3->get('cart')->find();
        $cart_total = 0;
        foreach($items as $item){
            $cart_total += $item->amount;
        }
        $f3->set('cart_total_cost', $cart_total);

        //TODO: Change column names in actor table to be compatible with multiple join on movies
        //Make certain there are movies to query
        if(!$f3->exists('movies')){
            $movies_query ="SELECT * 
                FROM movie
                JOIN genre 
                    ON movie.genre_id=genre.genre_id 
                JOIN director 
                    ON movie.director_id = director.director_id
                JOIN movie_actor
                    ON movie_actor.movie_movie_id = movie.movie_id
                JOIN actor
                    ON actor.actor_id = movie_actor.actor_actor_id";
            $f3->set('movies', $f3->get('db')->exec($movies_query));
        }

        //Filter movies
        $filtered_movies = array_filter($f3->get('movies'), function($movie){
            $count = 0;
            $goal = 0;

            ////Check if movie has title
            if( $_POST['movie_title']!="" ){
                $goal++;
                if( strpos( trim(strtolower($movie['title'])), trim(strtolower($_POST['movie_title'])) ) > -1 ){
                    $count ++;
                }
            }

            //Check if movie has director
            if( $_POST['movie_director']!="" ){
                $goal++;
                $director_name = $movie['first_name']." ".$movie['last_name'];
                if( strpos( trim(strtolower( $director_name )), trim(strtolower($_POST['movie_director'])) ) > -1 ){
                    $count ++;
                }
            }

            //Check if movie has actor
            if( $_POST['movie_actor']!="" ){
                $goal++;
                $actor_name = $movie['actor_first_name']." ".$movie['actor_last_name'];
                if( strpos( trim(strtolower( $actor_name )), trim(strtolower($_POST['movie_actor'])) ) > -1 ){
                    $count ++;
                }
            }

            ////Check if movie has genre
            if( $_POST['genre_id']!="" ){
                $goal++;
                if($movie['genre_id'] == $_POST['genre_id']){
                    $count++;
                }
            }

            return $count == $goal;
        });

        //Query genres here
        $f3->set('genres',$f3->get('db')->exec('SELECT * FROM genre'));

        //Set movies in view
        $f3->set('movies', $filtered_movies);

        //Display content
        $f3->set('content', 'templates/movies_list.htm');  
        echo \Template::instance()->render('templates/master.htm');

    }
);

$f3->route('GET /movies/@movieid',
    function($f3) {
        if($_SESSION['logged_in']==false){
            $f3->reroute('/login');
        }

        //Compute total cart cost here
        $items = $f3->get('cart')->find();
        $cart_total = 0;
        foreach($items as $item){
            $cart_total += $item->amount;
        }
        $f3->set('cart_total_cost', $cart_total);

    	$f3->set('page_title', 'Movies');   
        $f3->set('customer', true);
        $movieid = $f3->get('PARAMS.movieid');

    	//retrieve movie from database by id here
        $movie_query = "SELECT * FROM movie JOIN genre ON movie.genre_id=genre.genre_id JOIN director ON movie.director_id = director.director_id WHERE movie_id=".$movieid." ";
        $f3->set('movie', $f3->get('db')->exec($movie_query)[0]);
        print_r($f3->get('movie'));

        //Purchase info
        $f3->set('cart_total_cost', '8.00');
        $f3->set('cart_count', 4);


        //Query available formats and feed into this array
        $f3->set('formats_display_string', 'VHS, DVD, Blu-Ray, Digital');
        $f3->set('formats', array('VHS', 'DVD', 'Blu-Ray', 'Digital'));
    
        //Reviews    	
    	$reviews = array(
    		array('username'=>'jeff','movieid'=>$f3->get('PARAMS.movieid'), 'review'=>"I like this movie" ), 
    		array('username' =>'noonie', 'movieid'=>$f3->get('PARAMS.movieid'), 'review'=>"I don't like this movie"),
    		array('username' =>'oren', 'movieid'=>$f3->get('PARAMS.movieid'), 'review'=>"I don't like this movie")
    	);
        $f3->set('reviews', $reviews); 

    	$f3->set('content', 'templates/movie_detail.htm'); 
		echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /profile/@userid', 
    function($f3){
        if($_SESSION['logged_in']==false){
            $f3->reroute('/login');
        }
        $f3->set('password_succes', $_SESSION['password_succes']);

        $f3->set('page_title', 'Profile');
        $f3->set('customer', true);

        //Compute total cart cost here
        $items = $f3->get('cart')->find();
        $cart_total = 0;
        foreach($items as $item){
            $cart_total += $item->amount;
        }
        $f3->set('cart_total_cost', $cart_total);

        //Query user by id and get all related information
        $f3->set('username', ucfirst($_SESSION['username']));

        //Calculate total user debt here
        $f3->set('balance', $_SESSION['balance']);

        //Calculate preferred genre here
        $f3->set('preferred_genre', 'Anime');
        $f3->set('suggested_movie', 'Attack on Titan');
        $f3->set('movieid', 1);

        //User reviews
        $reviews = array(
            array('username'=>'jeff','moviename'=>'Some movie of a movie', 'review'=>"I like this movie", 'rating'=>5 ), 
            array('username' =>'noonie', 'moviename'=>'Some movie', 'review'=>"I don't like this movie", 'rating' =>6),
            array('username' =>'oren', 'moviename'=>'Some other movie', 'review'=>"I don't like this movie", 'rating' =>9)
        );
        $f3->set('reviews', $reviews); 
        $f3->set('content', 'templates/profile.htm');
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin', 
    function($f3){
        if($_SESSION['logged_in']==false){
            $f3->reroute('/login');
        }

        $f3->set('admin', true);
        $f3->set('content', 'templates/admin_home.htm');
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/@movieid/edit', 
    function($f3){
        if($_SESSION['logged_in']==false){
            $f3->reroute('/login');
        }
        
        $f3->set('page_title', 'Edit Movie');
        $f3->set('admin', true);
        $f3->set('content', 'templates/movie_inventory_edit.htm');

        //Query and pass movie data here
        $f3->set('title', 'Attack on Titan');

        //Query inventory and prices here
        $f3->set('digital', array(
            'rental' => '4',
            'purchase' => '29',
        ));
        $f3->set('vhs', array(
            'rental' => '4',
            'purchase' => '29',
            'inventory' => '80'
        ));
        $f3->set('dvd', array(
            'rental' => '4',
            'purchase' => '29',
            'inventory' => '30'
        ));
        $f3->set('bluray', array(
            'rental' => '4',
            'purchase' => '50',
            'inventory' => '20'
        ));

        $f3->set('available', true);

        $f3->set('title', 'Attack on Titan');
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/title',
    function($f3) {
        if($_SESSION['logged_in']==false){
            $f3->reroute('/login');
        }

        $f3->set('admin', true);
        $f3->set('page_title', 'Title Search'); 
        $f3->set('content', 'templates/title_search.htm'); 

        //Get inventory information
        $f3->set('vhs', array(
            'rental' => '4',
            'purchase' => '29',
            'inventory' => '80'
        ));
        $f3->set('dvd', array(
            'rental' => '4',
            'purchase' => '29',
            'inventory' => '30'
        ));
        $f3->set('bluray', array(
            'rental' => '4',
            'purchase' => '50',
            'inventory' => '20'
        ));

        $f3->set('movieid', 1);
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/reports/title', 
    function($f3){
        if($_SESSION['logged_in']==false){
            $f3->reroute('/login');
        }

        $f3->set('admin', true);
        $f3->set('content', 'templates/reports_title.htm');

        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/reports/genre', 
    function($f3){
        if($_SESSION['logged_in']==false){
            $f3->reroute('/login');
        }

        $f3->set('admin', true);
        $f3->set('content', 'templates/reports_genre.htm');

        //Query genres here
        $f3->set('genres', array('Horror', 'Action', 'Suspense', 'Romance', 'Sci-Fi', 'Drama'));

        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/reports/@genreid/@interval', 
    function($f3){
        if($_SESSION['logged_in']==false){
            $f3->reroute('/login');
        }

        $f3->set('admin', true);
        $f3->set('content', 'templates/report_genre.htm');

        $f3->set('genre', 'Horror');
        $f3->set('interval', 'Week');

        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/customer', 
    function($f3){
        if($_SESSION['logged_in']==false){
            $f3->reroute('/login');
        }

        $f3->set('admin', true);
        $f3->set('content', 'templates/customer.htm');

        $f3->set('outstandings', 
        array(
            array('title'=>'Jake\'s Out Fishing', 'rental'=> '3.50', 'fees'=>'20'), 
            array('title'=>'Bill and Ted\'s Awesome Adventure', 'rental'=> '3.50', 'fees'=>'20') 
        ));

        $f3->set('balance', '47');
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/@adminid/pricing', 
    function($f3){
        if($_SESSION['logged_in']==false){
            $f3->reroute('/login');
        }

        $f3->set('admin', true);
        $f3->set('content', 'templates/pricing.htm');

        $f3->set('new_release_price', '4');
        $f3->set('standard_price', '3.50');

        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->set('ONERROR',
    function($f3){
        ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    $f3->set('page_title', 'Page Not Found');
    $f3->set('content', 'templates/error.htm');
  echo \Template::instance()->render('templates/master.htm');
});

$f3->route('POST /movies/cart/add/@purchase_type/@movieid',
    function($f3){
        if($_SESSION['logged_in']==false){
            $f3->reroute('/login');
        }

        $movieid = $f3->get('PARAMS.movieid');
        $purchase_type = $f3->get('PARAMS.purchase_type');
        //If user did not exceed rentals
        // add item
        $movie_query = "SELECT * FROM movie WHERE movie_id=".$movieid;
        $movie = $f3->get('db')->exec($movie_query)[0];
        $f3->get('cart')->set('movieid', $movie_title);
        $f3->get('cart')->set('movie_title', $movie['title']);
        $amount = $purchase_type == 'rental' ? 4 : 34;
        $f3->get('cart')->set('amount',$amount);
        $f3->get('cart')->set('purchase_type',ucfirst($purchase_type));
        $f3->get('cart')->save();
        $f3->get('cart')->reset();
        $f3->reroute('/movies');
});

$f3->route('GET /movies/cart/empty', 
    function($f3){
        if($_SESSION['logged_in']==false){
            $f3->reroute('/login');
        }

        $f3->get('cart')->drop();
        $f3->reroute('/movies');
    }
);

$f3->route('GET /movies/cart/remove/@movieid', 
    function($f3){
        if($_SESSION['logged_in']==false){
            $f3->reroute('/login');
        }

        $movieid = $f3->get('PARAMS.movieid');
        $f3->get('cart')->erase('movieid', $movieid);
        $f3->reroute('/movies');
    }
);

$f3->run();
?>
