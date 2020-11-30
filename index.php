<?php
/***Problem resolution steps**/
//added apps: Fatfree Framework
//Allow All on apache2.conf.save on Directory
//Allow all on apache2 000-default.conf
//Create htaccess file from f3 
//Enable ssl on php.ini - dev and prod and standard
//!!! IMPORTANT !!!! SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY','') Must be run to disable full group by 
//Place into /etc/mysql/mysql.conf.d/mysqld.cnf, the lines:
//sql_mode = "STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION" - https://stackoverflow.com/questions/23921117/disable-only-full-group-by
//Movie API Key https://api.themoviedb.org/3/movie/550?api_key=46ba4d9debca18872c8aa769c630aab9
// URL: https://api.themoviedb.org/3/movie?sort_by=popularity.desc?550?api_key=46ba4d9debca18872c8aa769c630aab9
// Google api AIzaSyC6ctaf2rjK-sI61jYTUwp7ZiDfxDbbT8I
//COMMIT: Add session start
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require('connector.php');
$f3 = \Base::instance();
$f3->set('DEBUG', 3);


$apikey = "46ba4d9debca18872c8aa769c630aab9";
$f3->set('db', $db);
$f3->set('head', 'templates/head.htm');
$f3->set('navbar', 'templates/navbar.htm');
$f3->set('footscripts', 'templates/footscripts.htm'); 
$f3->set('footer', 'templates/footer.htm');
$f3->set('admin', false);
$f3->set('customer', false);
date_default_timezone_set('America/New_York');

$f3->set('cart', new \Basket());

//TODO: Title report calculations need to be calculated to verify working
//TODO: In cart values for rent need to be compared against the db and the pricing table
//TODO: Changed e-mail to email
$f3->route('POST /login', 
    function($f3){
        $username = $_POST['email'];
        $password = $_POST['password'];
        $email = "email@email.com";
        $user_query = "SELECT * FROM user WHERE email='".$email."' AND password='".$password."'";
        $user = $f3->get('db')->exec($user_query)[0];

        if( !empty($user) ){
            calculate_user_balance($f3, $user['user_id']);
            $_SESSION['logged_in'] = true;
            $_SESSION['userid'] = $user['user_id'];
            $_SESSION['balance'] = $user['balance'] == "" ? 0 : $user['balance'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['admin'] = false;
            $_SESSION['customer'] = true;
            $_SESSION['max_rentals_reached'] = false;
            $f3->reroute("/");
        }else{
            $_SESSION['logged_in'] = false;
            $f3->reroute("/login");
        }
    }
);

$f3->route('POST /admin/login', 
    function($f3){
        $f3->set('admin_login', true);
        $username = $_POST['username'];
        $password = $_POST['password'];
        $user_query = "SELECT * FROM employee WHERE username='".$username."' AND password='".$password."'";
        $user = $f3->get('db')->exec($user_query)[0];
        if( !empty($user) ){
            $_SESSION['logged_in'] = true;
            $_SESSION['admin'] = true;
            $_SESSION['customer'] = false;
            $_SESSION['employee_id'] = $user['employee_id'];
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

function verify_login($f3){
    if(!$_SESSION['logged_in']){
        $f3->reroute('/login');
    }
}

function verify_admin($f3){
    if(!$_SESSION['admin']){
        $f3->reroute("/login");
    }
}

$f3->route('GET /logout', 
    function($f3){
        session_destroy();
        $_SESSION['logged_in'] = false;
        $f3->set('page_title', 'Login');
        echo \Template::instance()->render('templates/login.htm');
    }
);

$f3->route('GET /',
    function($f3) {
        verify_login($f3);
        calculate_user_balance($f3, $_SESSION['userid']);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        if($_SESSION['customer']){
            update_cart($f3);
        }
        $get_user = "SELECT * FROM user WHERE user_id=".$_SESSION['userid'];
        $user = $f3->get('db')->exec($get_user)[0];
        $genres = $f3->get('db')->exec("SELECT * FROM genre ORDER BY genre_name ASC");
        $f3->set('genres', $genres);
        $f3->set('username', ucfirst($user['first_name']));
        $f3->set('customerid', $user['user_id']);
        $f3->set('page_title', 'Home');
        $f3->set('content', 'templates/customer_home.htm');
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /review/@customerid', 
    function($f3){
        verify_login($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        if($_SESSION['customer']){
            update_cart($f3);
        }
        $f3->set('page_title', 'Review');
        $customerid = $f3->get('PARAMS.customerid');
        $get_customer_rental_history = "SELECT DISTINCT movie.title, movie.movie_id FROM movie 
        LEFT JOIN inventory ON movie.movie_id=inventory.movie_id 
        JOIN rental ON rental.inventory_id=inventory.inventory_id 
        JOIN bill ON bill.transaction_id=rental.transaction_id  
        WHERE bill.user_id=".$customerid;

        $get_customer_movie_history = "SELECT DISTINCT movie.title, movie.movie_id FROM movie 
        LEFT JOIN inventory ON movie.movie_id=inventory.movie_id 
        JOIN purchase ON purchase.inventory_id=inventory.inventory_id 
        JOIN bill ON bill.transaction_id=purchase.transaction_id  
        WHERE bill.user_id=".$customerid;
        $movies  = $f3->get('db')->exec($get_customer_movie_history);
        $f3->set('movies', $movies);

        $f3->set('content', 'templates/review_list.htm');
        echo \Template::instance()->render('templates/master.htm');
    }
);

function calculate_user_balance($f3, $userid){
        $balance_query = "SELECT * FROM transaction 
        JOIN rental ON transaction.transaction_id=rental.transaction_id 
        WHERE transaction.user_id=".$userid." AND rental.current_status=0";
        $outstanding_rentals = $f3->get('db')->exec($balance_query);

        $balance = 0;
        $outstanding_array = [];
        foreach($outstanding_rentals as $rental){
            $current_date = new DateTime("now");
            $due_date = new DateTime($rental['due_datetime']);
            if($current_date > $due_date){
                $days = $due_date->diff($current_date)->format('%d');
                $inventory_id = $rental['inventory_id'];
                $query_movie_cost = "SELECT * FROM inventory JOIN movie ON "
                        . "movie.movie_id=inventory.movie_id WHERE "
                        . "inventory.inventory_id=".$inventory_id;
                $movie_data = $f3->get('db')->exec($query_movie_cost);
                $purchase_type = $movie_data[0]['inventory_type'];
                $outstanding_array[$movie_data[0]['title']]['title'] = $movie_data[0]['title'];
                $outstanding_array[$movie_data[0]['title']]['inventory_id'] = $movie_data[0]['inventory_id'];

                $cost = 0;
                if($purchase_type!='digital'){
                    if($days>15){
                        switch($purchase_type){
                            case "vhs":
                                $cost = $movie_data[0]['vhs_purchase'] - $movie_data[0]['vhs_rental'];
                                $outstanding_array[$movie_data[0]['title']]['rental'] = $movie_data[0]['vhs_rental']; 
                                break;
                            case "dvd":
                                $cost = $movie_date[0]['dvd_purchase'] - $movie_data[0]['dvd_rental'];
                                $outstanding_array[$movie_data[0]['title']]['rental'] = $movie_data[0]['dvd_rental'];
                                break;
                            case "bluray":
                                $cost = $movie_data[0]['bluray_purchase'] - $movie_data[0]['bluray_rental'];
                                $outstanding_array[$movie_data[0]['title']]['rental'] = $movie_data[0]['bluray_rental'];
                                break;
                        }
                        $outstanding_array[$movie_data[0]['title']]['fees'] = $cost;
                         
                    }else{
                        switch($purchase_type){
                            case "vhs":
                                $outstanding_array[$movie_data[0]['title']]['rental'] = $movie_data[0]['vhs_rental']; 
                                break;
                            case "dvd":
                                $outstanding_array[$movie_data[0]['title']]['rental'] = $movie_data[0]['dvd_rental'];
                                break;
                            case "bluray":
                                $outstanding_array[$movie_data[0]['title']]['rental'] = $movie_data[0]['bluray_rental'];
                                break;
                        }
                        $outstanding_array[$movie_data[0]['title']]['fees'] = ($days*2);
                        $balance += ($days*2);
                    }
                }else{
                    //Update rental table to reflect return date
                    $return_digital = "UPDATE rental 
                    SET return_datetime='".$current_date->format('Y-m-d')."',
                    current_status=1  
                    WHERE rental.transaction_id=".$rental['transaction_id'];
                    $f3->get('db')->exec($return_digital);

                }
            }
        }
        $_SESSION['balance'] = $balance;

        return $outstanding_array;
}


function query_movie_poster($title, $date){
    $url_query_title = str_replace(" ", "+", $title);
    $year = new DateTime($date);
    $year = $year->format('Y');

    $url = "https://api.themoviedb.org/3/search/movie?query=".$url_query_title."&year=".$year."&api_key=46ba4d9debca18872c8aa769c630aab9&language=en-US&page=1&include_adult=false";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 80);

    $response = curl_exec($ch);

    if(curl_error($ch)){
        return 'Request Error:' . curl_error($ch);
    }
    else
    {
        $obj = json_decode($response);
        return "https://image.tmdb.org/t/p/w500".$obj->results[0]->poster_path;
    }

    curl_close($ch);

}
$f3->route('GET /movies',
    function($f3) {
        verify_login($f3);
        $f3->set('customer', $_SESSION['customer']);
        if($_SESSION['customer']){
            calculate_user_balance($f3, $_SESSION['userid']);
            update_cart($f3);
        }
        $f3->set('admin', $_SESSION['admin']);
        $f3->set('found', true);

//1661, 1181, 1598, 1058, 1687, 1237, 1659, 1406

        // $get_featured_movies = "SELECT * FROM movie WHERE 
        // movie_id=1661 OR movie_id=1181 OR movie_id=1598 OR movie_id=1058 OR movie_id=1687 OR 
        // movie_id=1237 OR movie_id=1659 OR movie_id=1406";

        // $featured_movies = $f3->get('db')->exec($get_featured_movies);
        // foreach($featured_movies as $feature){
        //     $url = query_movie_poster($feature['title'], $feature['date_released']);
        // }



        //General page values
    	$f3->set('page_title', 'Movies');

        //Query genres here
        $genres = $f3->get('db')->exec('SELECT * FROM genre ORDER BY genre_name ASC');
        $f3->set('genres', $genres);

        $grouped_movies = array();
        foreach($genres as $genre){
            $grouped_movies[$genre['genre_name']] = $f3->get('db')->exec("SELECT * FROM movie WHERE genre_id=".$genre['genre_id']." ORDER BY movie.title ASC"); 
            foreach($grouped_movies[$genre['genre_name']] as $key=>$movie){
                $release_date = $movie['date_released'];
                $grouped_movies[$genre['genre_name']][$key]['new_release'] = is_new_release($release_date);
            }
        }


        $f3->set('grouped_movies', $grouped_movies);

        //Initialize movies
        // $movies_query ='SELECT * FROM movie JOIN genre ON movie.genre_id=genre.genre_id JOIN director ON movie.director_id = director.director_id';
        // $movies = $f3->get('db')->exec($movies_query);

        // foreach($movies as $key=>$movie){
        //     $release_date = $movie['date_released'];
        //     $movies[$key]['new_release'] = is_new_release($release_date);
        // }

        // $f3->set('movies', $movies);

        //Display the page
    	$f3->set('content', 'templates/movies_list.htm');  
		echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /profile/return/@userid', 
    function($f3){
        //Rentals already in user's possession
        $query_rentals_outstanding = "SELECT * FROM transaction 
            JOIN rental ON transaction.transaction_id=rental.transaction_id 
            JOIN inventory ON rental.inventory_id=inventory.inventory_id
            WHERE transaction.user_id=".$_SESSION['userid']." AND rental.current_status=0 
            GROUP BY transaction.transaction_id";

        $date = new DateTime();
        $date = $date->format('Y-m-d');
        //Rentals in cart
        $rentals_outstanding = $f3->get('db')->exec($query_rentals_outstanding);
        foreach($rentals_outstanding as $rental){
            //Rental update
            $update_rental = "UPDATE rental SET current_status=1, return_datetime='".$date."' WHERE transaction_id=".$rental['transaction_id'];
            $f3->get('db')->exec($update_rental);

            //Inventory upate
            $get_inventory_count = "SELECT inventory_count FROM inventory WHERE inventory_id=".$rental['inventory_id'];
            $inventory_count = $f3->get('db')->exec($get_inventory_count)[0]['inventory_count'];
            $inventory_count++;
            $update_inventory = "UPDATE inventory SET inventory_count=".$inventory_count." WHERE inventory_id=".$rental['inventory_id'];
            $f3->get('db')->exec($update_inventory);
        }
        $_SESSION['max_rentals_reached'] = false;
        $f3->reroute("/profile/".$_SESSION['userid']);        
    }
);

$f3->route('POST /profile/@userid/update', 
    function($f3){
        verify_login($f3);
        update_cart($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        $_SESSION['password_success'] = false;

        $password = $_POST['password'];
        $userid = $f3->get('PARAMS.userid');
        $update_stmt = "UPDATE user SET password='".$password."' WHERE user_id=".$userid;
        if($f3->get('db')->exec($update_stmt)>0){
            $_SESSION['password_success'] = true;
        }
        $_SESSION['show_pass_message'] = true;
        $route = "/profile/".$f3->get('PARAMS.userid');

        $f3->reroute($route);
    }
);


$f3->route('POST /movies/query', 
    function($f3){
        verify_login($f3);
        $f3->set('customer', $_SESSION['customer']);
        if($_SESSION['customer']){
            update_cart($f3);
        }
        $f3->set('admin', $_SESSION['admin']);
        $f3->set('page_title', 'Search Movie');

        //TODO: Changed column names in actor table to be compatible with multiple join on movies
        //Make certain there are movies to query
        if(!$f3->exists('movies')){
            $movies_query ="SELECT movie.movie_id, movie.genre_id, director.director_id, movie.title, movie.description, movie.date_released, movie.rental_period, movie.digital_rental,movie.digital_purchase, movie.vhs_rental, movie.vhs_purchase, movie.dvd_rental, movie.dvd_purchase, movie.bluray_rental, movie.bluray_purchase, movie.available, genre.genre_name, director.first_name, director.last_name, movie_actor.movie_movie_id, movie_actor.actor_actor_id, actor.actor_id, actor.actor_last_name, actor.actor_first_name
                FROM movie
                JOIN genre 
                    ON movie.genre_id=genre.genre_id 
                JOIN director 
                    ON movie.director_id = director.director_id
                JOIN movie_actor
                    ON movie_actor.movie_movie_id = movie.movie_id
                JOIN actor
                    ON actor.actor_id = movie_actor.actor_actor_id
                GROUP BY movie.movie_id ORDER BY movie.title ASC";
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
            if( $_POST['movie_genre']!="genre" ){
                $goal++;
                if($movie['genre_id'] == $_POST['movie_genre']){
                    $count++;
                }
            }

            return $count == $goal;
        });

        $f3->set('found', count($filtered_movies)>0);

        //Query genres here
        $genres = $f3->get('db')->exec('SELECT * FROM genre ORDER BY genre_name ASC');
        $f3->set('genres', $genres);

        //Group movies into genres
        $grouped_movies = array();
        foreach($genres as $genre){
            $grouped_movies[$genre['genre_name']] = array_filter($filtered_movies, function ($movie) use ($genre){
                return $movie['genre_id'] == $genre['genre_id'];
            }); 
            foreach($grouped_movies[$genre['genre_name']] as $key=>$movie){
                $release_date = $movie['date_released'];
                $grouped_movies[$genre['genre_name']][$key]['new_release'] = is_new_release($release_date);
            }
        }


        $f3->set('grouped_movies', $grouped_movies);

        //Set movies in view
        $f3->set('movies', $filtered_movies);

        //Display content
        $f3->set('content', 'templates/movies_list.htm');  
        echo \Template::instance()->render('templates/master.htm');

    }
);

function update_cart($f3){
    //Calculate cart rental count
    $get_rentals="SELECT COUNT(*) FROM transaction JOIN rental ON transaction.transaction_id=rental.transaction_id WHERE transaction.user_id=".$_SESSION['userid']." AND rental.current_status=0";
    $number_rentals_outstanding = $f3->get('db')->exec($get_rentals)[0]['COUNT(*)'];
    $rentals_in_cart = count($f3->get('cart')->find('purchase_type', 'Rental'));
    $total_rentals = $number_rentals_outstanding + $rentals_in_cart;
    $f3->set('rental_count', $total_rentals);
    $rentals_available = 2-$total_rentals;
    if($rentals_available == 0){
        $f3->set('cart_info', 'You have reached your maximum 2 rentals at a time');
    }else{
        $f3->set('cart_info', "You can add ".$rentals_available." rental(s) to your cart");
        $_SESSION['max_rentals_reached'] = false;
    }

    //Compute total cart cost here
    $items = $f3->get('cart')->find();
    $cart_total = 0;
    foreach($items as $item){
        $cart_total += $item->amount;
    }
    $f3->set('cart_total_cost', $cart_total);    
}


$f3->route('GET /movies/@movieid',
    function($f3) {
        verify_login($f3);
        if($_SESSION['customer']){
            update_cart($f3);
        }
    	$f3->set('page_title', 'Movies');
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);   
        $movieid = $f3->get('PARAMS.movieid');
        $userid = $_SESSION['userid'];

        $f3->set('userid', $_SESSION['userid']);
        $f3->set('movieid', $movieid);

    	//retrieve movie from database by id here
        $movie_query = "SELECT * FROM movie 
        JOIN genre ON movie.genre_id=genre.genre_id 
        JOIN director ON movie.director_id = director.director_id  
        WHERE movie.movie_id=".$movieid;

        $movie = $f3->get('db')->exec($movie_query)[0];
        $f3->set('page_title', 'View Movie');
        $f3->set('available', $movie['available']);

        //Query image from API
        $url_query_title = str_replace(" ", "+", $movie['title']);
        $year = new DateTime($movie['date_released']);
        $year = $year->format('Y');

        $url = "https://api.themoviedb.org/3/search/movie?query=".$url_query_title."&year=".$year."&api_key=46ba4d9debca18872c8aa769c630aab9&language=en-US&page=1&include_adult=false";
        $dataArray = array();
        $ch = curl_init();
        $data = http_build_query($dataArray);
        $getUrl = $url."?".$data;
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 80);

        $response = curl_exec($ch);

        if(curl_error($ch)){
            echo 'Request Error:' . curl_error($ch);
        }
        else
        {
            $obj = json_decode($response);
            $movie['image_url'] = "https://image.tmdb.org/t/p/w500".$obj->results[0]->poster_path;
        }

        curl_close($ch);

        //Query video from API
        $url_query_title = str_replace(" ", "+", $movie['title']);
        $year = new DateTime($movie['date_released']);
        $year = $year->format('Y');

        $encoded = urlencode($movie['title']);
        $url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=$encoded&key=AIzaSyC6ctaf2rjK-sI61jYTUwp7ZiDfxDbbT8I";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 80);

        $response = curl_exec($ch);

        if(curl_error($ch)){
            echo 'Request Error:' . curl_error($ch);
        }
        else
        {
            $obj = json_decode($response);
            // print_r($obj);
            if(!$obj->error->code){
                foreach($obj->items as $item){
                    if($item->snippet->channelTitle=="Movieclips"){
                        $movie['video'] = "https://www.youtube.com/embed/".$item->id->videoId;
                        break;
                    }
                }
            }
        }

        curl_close($ch);

        $f3->set('movie', $movie);

        //Query available formats and feed into this array
        $f3->set('formats_display_string', 'VHS, DVD, Blu-Ray, Digital');
        $f3->set('formats', array('VHS', 'DVD', 'Blu-Ray', 'Digital'));
    
        //Reviews  
        $get_reviews = "SELECT * FROM review 
        JOIN user ON review.review_user_id=user.user_id 
        WHERE review.review_movie_id=".$movieid." 
        AND user.user_id=".$userid;
        $reviews = $f3->get('db')->exec($get_reviews);

        //Cast members
        $get_cast = "SELECT * FROM movie_actor 
        -- JOIN movie_actor ON movie.movie_id=movie_actor.movie_movie_id 
        JOIN actor ON actor.actor_id=movie_actor.actor_actor_id 
        WHERE movie_actor.movie_movie_id=".$movie['movie_id'];
        $cast = $f3->get('db')->exec($get_cast);
        $cast_string = '';
        foreach($cast as $member){
            $cast_string .= ucfirst($member['actor_first_name'])." ".ucfirst($member['actor_last_name']).", ";
        }
        $f3->set('cast', rtrim($cast_string, ", "));

        $f3->set('reviews', $reviews); 

    	$f3->set('content', 'templates/movie_detail.htm'); 
		echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /profile/@userid', 
    function($f3){
        verify_login($f3);
        update_cart($f3);
        calculate_user_balance($f3, $f3->get('PARAMS.userid'));
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        $userid = $f3->get('PARAMS.userid');
        if($userid != $_SESSION['userid']){
            $f3->reroute('/error');
        }
        $f3->set('page_title', 'Profile');

        
        //Legacy, password change not useful in this context
        if(strpos($f3->get('SERVER.HTTP_REFERER'), "profile/".$userid) &&
            $_SESSION['show_pass_message']
        ){
            $f3->set('show_pass_message', true);
        }else{
            $f3->set('show_pass_message', false);
        }


        //Query user by id and get all related information
        $get_user = "SELECT * FROM user WHERE user_id=".$_SESSION['userid'];
        $user = $f3->get('db')->exec($get_user);
        $username = ucfirst($user[0]['first_name']);
        $f3->set('username', $username);

        //Calculate total user debt here
        $f3->set('balance', $_SESSION['balance']);

        //Calculate preferred genre here
        $rental_history_query = "SELECT COUNT(g.genre_name), g.genre_name FROM transaction  
        JOIN rental ON transaction.transaction_id=rental.transaction_id 
        JOIN inventory ON inventory.inventory_id=rental.inventory_id 
        JOIN movie ON movie.movie_id=inventory.movie_id 
        JOIN genre AS g ON g.genre_id=movie.genre_id 
        WHERE user_id=".$_SESSION['userid']."
        GROUP BY g.genre_name";



        $purchase_history_query = "SELECT COUNT(g.genre_name), g.genre_name FROM transaction  
        JOIN purchase ON transaction.transaction_id=purchase.transaction_id 
        JOIN inventory ON inventory.inventory_id=purchase.inventory_id 
        JOIN movie ON movie.movie_id=inventory.movie_id 
        JOIN genre AS g ON g.genre_id=movie.genre_id 
        WHERE user_id=".$_SESSION['userid']."
        GROUP BY g.genre_name";

        $rental_history = $f3->get('db')->exec($rental_history_query);
        $purchase_history = $f3->get('db')->exec($purchase_history_query);

        //Calculate highest viewed genre
        $genres_query = "SELECT genre_name FROM genre";
        $genres = $f3->get('db')->exec($genres_query);
        $genre_statistics = [];
        foreach($genres as $genre=>$gval){
            $genre_statistics[$gval['genre_name']]['count'] = 0;
            foreach($rental_history as $rental=>$rval){
                if($rval['genre_name'] == $gval['genre_name']){
                    $count = $genre_statistics[$gval['genre_name']]['count'];
                        $count += $rval["COUNT(g.genre_name)"];
                        $genre_statistics[$gval['genre_name']]['count'] = $count;
                    }
            }
            foreach($purchase_history as $purchase=>$pval){
                if($pval['genre_name'] == $gval['genre_name']){
                    $count = $genre_statistics[$gval['genre_name']]['count'];
                    $count += $pval["COUNT(g.genre_name)"];
                    $genre_statistics[$gval['genre_name']]['count'] = $count;
                }                
            }
        }
        $highest_viewed_genre = array_search(max($genre_statistics), $genre_statistics);
        $f3->set('preferred_genre', $highest_viewed_genre);

        $suggested_movie_query = "SELECT MAX(date_released), title, movie_id FROM movie 
        JOIN genre 
        ON movie.genre_id=genre.genre_id
        WHERE genre.genre_name='".$highest_viewed_genre."'
        GROUP BY title, movie_id";
        $suggested_movie = $f3->get('db')->exec($suggested_movie_query);
        $f3->set('suggested_movie', $suggested_movie[0]['title']);;
        $f3->set('movieid', $suggested_movie[0]['movie_id']);

        //User reviews
        $user_reviews_query = "SELECT * FROM review 
        JOIN movie 
        ON movie.movie_id=review.review_movie_id
        WHERE review_user_id=".$_SESSION['userid'];
        $user_reviews = array_map(function($review){
            return [
                'moviename'=> $review['title'],
                'review'=> $review['review_text'], 
                'rating'=> $review['review_stars']
            ];

        },$f3->get('db')->exec($user_reviews_query));
        $f3->set('reviews', $user_reviews);

        //Rentals checked out
        $balance_query = "SELECT * FROM transaction 
        JOIN rental ON transaction.transaction_id=rental.transaction_id 
        JOIN inventory ON inventory.inventory_id=rental.inventory_id 
        JOIN movie ON movie.movie_id=inventory.movie_id 
        WHERE transaction.user_id=".$userid." AND rental.current_status=0";
        $rentals_checkedout = $f3->get('db')->exec($balance_query);
        foreach($rentals_checkedout as $key=>$rental){
            $date = new DateTime($rental['due_datetime']);
            $date = $date->format('m/d/Y');        
            $rentals_checkedout[$key]['due'] = $date;

            $current_date = Date('Y-m-d H:i:s');
            $rentals_checkedout[$key]['late'] = Date('Y-m-d H:i:s', strtotime($current_date)) > Date('Y-m-d H:i:s', strtotime($rental['due_datetime']));
        }

        $f3->set('checked_out', $rentals_checkedout);
        $f3->set('content', 'templates/profile.htm');
        echo \Template::instance()->render('templates/master.htm');
    }
);

function cleanInput($input) {
 
  $search = array(
    '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
    '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
    '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
    '@<![\s\S]*?--[ \t\n\r]*>@',
    '@\'@'
         // Strip multi-line comments
  );
 
    $output = preg_replace($search, '', $input);
    return $output;
  }

  function sanitize($input) {
    if (is_array($input)) {
        foreach($input as $var=>$val) {
            $output[$var] = sanitize($val);
        }
    }
    else {
        // if (get_magic_quotes_gpc()) {
        //     $input = stripslashes($input);
        // }
        $input  = cleanInput($input);
        $output = $input;
    }
    return $output;
}

$f3->route('POST /movie/@userid/review/@movieid', 
    function($f3){
        $userid = $f3->get('PARAMS.userid');
        $movieid = $f3->get('PARAMS.movieid');
        $review = sanitize($_POST['review']);
        $rating = $_POST['rating'];
        print_r($review);
        $date = new DateTime();
        $date = $date->format('Y-m-d');


        $update_review = "INSERT INTO review (review_user_id, review_movie_id, review_stars, review_text, review_date) VALUES(".$userid.", ".$movieid.", ".$rating.", '".$review."', '".$date."')";
        $f3->get('db')->exec($update_review);

        $f3->reroute("/movies/".$movieid);
    }
);

$f3->route('GET /admin', 
    function($f3){
        verify_login($f3);
        verify_admin($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        $f3->set('page_title', 'Home');

        $f3->set('content', 'templates/admin_home.htm');
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/@movieid/edit', 
    function($f3){
        verify_login($f3);
        verify_admin($f3);

        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        
        $f3->set('page_title', 'Edit Movie');
        $f3->set('admin', true);
        $f3->set('content', 'templates/movie_inventory_edit.htm');

        //Query and pass movie data here
        $movie_query = "SELECT * FROM movie 
        JOIN inventory
        ON movie.movie_id=inventory.movie_id
        WHERE movie.movie_id=".$f3->get('PARAMS.movieid');
        $movie = $f3->get('db')->exec($movie_query);
        $f3->set('title', $movie['title']);
        $vhs_movie = $movie[0];
        $dvd_movie = $movie[1];
        $bluray_movie = $movie[2];
        $digital_movie = $movie[3];

        //Query inventory and prices here
        $f3->set('digital', array(
            'rental' => $movie[0]['digital_rental'],
            'purchase' => $movie[0]['digital_purchase']
        ));
        $f3->set('vhs', array(
            'rental' => $movie[0]['vhs_rental'],
            'purchase' => $movie[0]['vhs_purchase'],
            'inventory' => $vhs_movie['inventory_count'],
            'location' => $vhs_movie['location']
        ));
        $f3->set('dvd', array(
            'rental' => $movie[0]['dvd_rental'],
            'purchase' => $movie[0]['dvd_purchase'],
            'inventory' => $dvd_movie['inventory_count'],
            'location' => $dvd_movie['location']
        ));
        $f3->set('bluray', array(
            'rental' => $movie[0]['bluray_rental'],
            'purchase' => $movie[0]['bluray_purchase'],
            'inventory' => $bluray_movie['inventory_count'],
            'location' => $bluray_movie['location']
        ));

        $f3->set('available', $movie[0]['available']);
        $f3->set('movieid', $f3->get('PARAMS.movieid'));

        //TODO: LAST TODO!!! Add delete movie functionality
        $f3->set('title', $movie[0]['title']);
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/movie/add', 
    function($f3){
        verify_login($f3);
        verify_admin($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        $f3->set('page_title', 'Add Movie'); 
        $f3->set('content', 'templates/add_movie.htm');

        $get_actors = "SELECT * FROM actor";
        $get_directors = "SELECT * FROM director";
        $get_genres = "SELECT * FROM genre";

        $f3->set('genres', $f3->get('db')->exec($get_genres));
        $f3->set('actors', $f3->get('db')->exec($get_actors));
        $f3->set('directors', $f3->get('db')->exec($get_directors));

        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('POST /admin/movie/add', 
    function($f3){
        verify_login($f3);
        verify_admin($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        $f3->set('page_title', 'Add Movie'); 

        switch($_POST['action']){
            case "add":
                $movie_title = $_POST['movie_title'];
                $genreid = $_POST['movie_genre'];
                $release_date = $_POST['release_date'];
                $synopsis = $_POST['synopsis'];
                
                //Get the director
                $director_id = '';
                if($_POST['director'] == "New Director"){
                    $director_first_name = $_POST['director_first_name'];
                    $director_last_name = $_POST['director_last_name'];

                    $create_new_director =  "INSERT INTO director(first_name, last_name) 
                    VALUES(".$director_first_name.", ".$director_last_name.")";
                    $f3->get('db')->exec($create_new_director);

                    $get_director = "SELECT MAX(director_id) FROM director";
                    $directorid = $f3->get('db')->exec($get_director)[0]['director_id'];  
                }else{
                    $directorid = $_POST['director'];
                }

                //Add movie to movie table
                $get_last_movie_id = "SELECT MAX(movie_id) FROM movie";
                $last_movie_id = $f3->get('db')->exec($get_last_movie_id)[0]['MAX(movie_id)'];
                $movie_id = $last_movie_id + 1;

                $add_movie = "INSERT INTO movie(movie_id, title, genre_id, director_id, description, date_released) 
                VALUES(".$movie_id.",'".$movie_title."', ".$genreid.", ".$directorid.", '".$synopsis."', '".$release_date."')";
                $f3->get('db')->exec($add_movie);

                //Add actor to movie_actor
                $actorid = $_POST['actor'];
                $add_movie_actor = "INSERT INTO movie_actor(movie_movie_id, actor_actor_id) 
                VALUES(".$movie_id.", ".$actorid.")";
                $f3->get('db')->exec($add_movie_actor);

                /******************ADD MOVIE INVENTORY*****************************/
                $get_genre_name = "SELECT genre_name FROM genre WHERE genre_id=".$genreid;
                $genre_name = $f3->get('db')->exec($get_genre_name)[0]['genre_name'];
                $insert_pre = "INSERT INTO inventory(movie_id, inventory_type, inventory_count, location) VALUES(".$movie_id;

                //Insert VHS inventory
                $insert_vhs = $insert_pre.", 'vhs', 0, 'V".$genreid."".$movie_id."')";
                $f3->get('db')->exec($insert_vhs);

                //Insert DVD inventory
                $insert_dvd = $insert_pre.", 'dvd', 0, 'D".$genreid."".$movie_id."')";
                $f3->get('db')->exec($insert_dvd);

                //Insert Bluray inventory
                $insert_bluray = $insert_pre.", 'bluray', 0, 'BL".$genreid."".$movie_id."')";
                $f3->get('db')->exec($insert_bluray);                


                $route = "/admin/title/".$movie_id;
                $f3->reroute($route);
            case "close":
                $route = "/admin";
                $f3->reroute($route);
            break;
        }
    }
);
$f3->route('POST /admin/@movieid/edit', 
    function($f3){
        verify_login($f3);
        verify_admin($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        $f3->set('page_title', 'Edit Movie'); 
        $f3->set('content', 'templates/title_search.htm'); 
        $movieid = $f3->get('PARAMS.movieid');

        $update_movie_exec = "UPDATE movie SET";

        switch($_POST['action']){
            case "save":
                $exec = 0;
                if(trim($_POST['vhs_inventory_count']) != ""){
                    $vhs_update = $_POST['vhs_inventory_count']."
                     WHERE movie_id=".$movieid." AND inventory_type='vhs'";
                    $update_movie_inventory_exec="UPDATE inventory SET inventory_count=".$vhs_update;
                    $f3->get('db')->exec($update_movie_inventory_exec);
                }
                if(trim($_POST['dvd_inventory_count']) != ""){
                    $dvd_update = $_POST['dvd_inventory_count']."
                     WHERE movie_id=".$movieid." AND inventory_type='dvd'";
                    $update_movie_inventory_exec = "UPDATE inventory SET inventory_count=".$dvd_update;
                    $f3->get('db')->exec($update_movie_inventory_exec);
                }
                if(trim($_POST['bluray_inventory_count']) != ""){
                    $bluray_update = $_POST['bluray_inventory_count']." WHERE movie_id=".$movieid." AND inventory_type='bluray'";
                    $update_movie_inventory_exec = "UPDATE inventory SET inventory_count=".$bluray_update;
                    $f3->get('db')->exec($update_movie_inventory_exec);   
                }
                if(trim($_POST['vhs_rental_cost']) != ""){
                    $vhs_rental_update=" vhs_rental=".$_POST['vhs_rental_cost'];
                    $update_movie_exec .= $vhs_rental_update;
                    $exec++;
                }
                if(trim($_POST['vhs_purchase_cost']) != ""){
                    $seperator = $exec > 0 ? ", " : "";
                    $vhs_purchase_update = $seperator." vhs_purchase=".$_POST['vhs_purchase_cost'];
                    $update_movie_exec .= $vhs_purchase_update;  
                    $exec++;                  
                }
                if(trim($_POST['dvd_rental_cost']) != ""){
                    $seperator = $exec > 0 ? ", " : "";
                    $dvd_rental_update = $seperator." dvd_rental=".$_POST['dvd_rental_cost'];
                    $update_movie_exec .= $dvd_rental_update;
                    $exec++;                    
                }
                if(trim($_POST['dvd_purchase_cost']) != ""){
                    $seperator = $exec > 0 ? ", " : "";
                    $dvd_purchase_update = $seperator." dvd_purchase=".$_POST['dvd_purchase_cost'];
                    $update_movie_exec .= $dvd_purchase_update; 
                    $exec++;                    
                }
                if(trim($_POST['bluray_rental_cost']) != ""){
                    $seperator = $exec > 0 ? ", " : "";
                    $bluray_rental_update .= $seperator." bluray_rental=".$_POST['bluray_rental_cost'];
                    $update_movie_exec .= $bluray_rental_update;  
                    $exec++;                    
                }
                if(trim($_POST['bluray_purchase_cost']) != ""){
                    $seperator = $exec > 0 ? ", " : "";
                    $bluray_purchase_update = $seperator." bluray_purchase=".$_POST['bluray_purchase_cost'];
                    $update_movie_exec .= $bluray_purchase_update;
                    $exec++;                      
                }
                if(trim($_POST['digital_rental_cost']) != ""){
                    $seperator = $exec > 0 ? ", " : "";
                    $digital_rental_update = $seperator." digital_rental=".$_POST['digital_rental_cost'];
                    $update_movie_exec .= $digital_rental_update;
                    $exec++;                    
                }
                if(trim($_POST['digital_purchase_cost']) != ""){
                    $seperator = $exec > 0 ? ", " : "";
                    $digital_purchase_update = $seperator." digital_purchase=".$_POST['digital_purchase_cost'];
                    $update_movie_exec .= $digital_purchase_update;
                    $exec++;
                }
                if(trim($_POST['availability']) != ""){
                    $seperator = $exec > 0 ? ", " : "";
                    $update_movie_exec .=$seperator." available=1";
                    $exec++;
                }else{
                    $seperator = $exec > 0 ? ", " : "";
                    $update_movie_exec .=$seperator." available=0";
                    $exec++;
                }

                if($exec > 0){
                    $update_movie_exec .=" WHERE movie_id=".$movieid."";
                    $f3->get('db')->exec($update_movie_exec);
                }
                $route = "/admin/title/".$movieid;
                $f3->reroute($route);
            case "close":
                $route = "/admin/title/".$movieid;
                $f3->reroute($route);
            break;
            case "delete":
                $get_movie_title = "SELECT title FROM movie WHERE movie_id=".$movieid;
                $movie_title = $f3->get('db')->exec($get_movie_title)[0]['title'];

                //Remove movie from inventory table
                $delete_movie_inventory = "SET FOREIGN_KEY_CHECKS=0;
                DELETE FROM inventory WHERE movie_id=".$movieid.";
                SET FOREIGN_KEY_CHECKS=1;";
                $f3->get('db')->exec($delete_movie_inventory);

                //Remove movie from movie table
                $delete_movie = "DELETE FROM movie WHERE movie_id=".$movieid;
                $f3->get('db')->exec($delete_movie);

                $reroute = "admin/movie/delete_confirm/".$movie_title;
                $f3->reroute($reroute);

            break;
        }
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/movie/delete_confirm/@title', 
    function($f3){
        verify_login($f3);
        verify_admin($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        $title = $f3->get('PARAMS.title');
        $f3->set('content', 'templates/movie_delete_confirmation.htm');
        $f3->set('title', $title);        
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/title',
    function($f3) {
        verify_login($f3);
        verify_admin($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        $f3->set('found', true);

        //General page values
        $f3->set('page_title', 'Title Search');

        //Query genres here
        $f3->set('genres',$f3->get('db')->exec('SELECT * FROM genre'));

        //Initialize movies
        $movies_query ='SELECT * FROM movie 
        JOIN genre ON movie.genre_id=genre.genre_id JOIN director ON movie.director_id = director.director_id';
        $f3->set('movies', $f3->get('db')->exec($movies_query));

        //Display the page
        $f3->set('content', 'templates/movies_list.htm');  
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/title/@movieid',
    function($f3) {
        verify_login($f3);
        verify_admin($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);

        $f3->set('page_title', 'Movie Info'); 
        $f3->set('content', 'templates/title_search.htm'); 

        $movie_query = "SELECT * FROM movie 
        JOIN inventory ON movie.movie_id=inventory.movie_id
        WHERE movie.movie_id=".$f3->get('PARAMS.movieid');
        $movie = $f3->get('db')->exec($movie_query);
        $vhs_inventory = $movie[0];
        $dvd_inventory = $movie[1];
        $bluray_inventory = $movie[2];
        $movie = $movie[0];

        $f3->set('title_searched', true);
        $f3->set('title', $movie['title']);
        
        //Get inventory information
        $f3->set('vhs', array(
            'rental' => $movie['vhs_rental'],
            'purchase' => $movie['vhs_purchase'],
            'inventory' => $vhs_inventory['inventory_count'],
            'location' => $vhs_inventory['location']
        ));
        $f3->set('dvd', array(
            'rental' => $movie['dvd_rental'],
            'purchase' => $movie['dvd_purchase'],
            'inventory' => $dvd_inventory['inventory_count'],
            'location' => $dvd_inventory['location']
        ));
        $f3->set('bluray', array(
            'rental' => $movie['bluray_rental'],
            'purchase' => $movie['bluray_purchase'],
            'inventory' => $bluray_inventory['inventory_count'],
            'location' => $bluray_inventory['location']
        ));
        $f3->set('digital', array(
            'rental' => $movie['digital_rental'],
            'purchase' => $movie['digital_purchase'],
            'location' => $digital_inventory['location']
        ));


        $f3->set('available', $movie['available']);
        $f3->set('movieid', $f3->set('movieid', $f3->get('PARAMS.movieid')));
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/reports/title', 
    function($f3){
        verify_login($f3);
        verify_admin($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        $f3->set('page_title', 'Title Reports');
        $f3->set('genres', $f3->get('db')->exec("SELECT * FROM genre ORDER BY genre_name ASC"));
        $f3->set('found', true);
        $f3->set('content', 'templates/reports_title.htm');

        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/reports/title/@movieid', 
    function($f3){
        verify_login($f3);
        verify_admin($f3);

        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        $f3->set('page_title', 'Title Report');
        $f3->set('genres', $f3->get('db')->exec("SELECT * FROM genre ORDER BY genre_name ASC"));
        $get_movie_title = "SELECT title FROM movie WHERE movie_id=".$f3->get('PARAMS.movieid');
        $f3->set('movie_title', $f3->get('db')->exec($get_movie_title)[0]['title']);

        //Gather all rental invoices for the movie3
        $rental_invoices = [];
        // $movie_rental_query = "SELECT * FROM movie 
        // LEFT JOIN inventory ON movie.movie_id=inventory.movie_id 
        // LEFT JOIN rental ON rental.inventory_id=inventory.inventory_id 
        // LEFT JOIN bill ON bill.transaction_id=rental.transaction_id 
        // LEFT JOIN invoice ON invoice.invoice_id=bill.invoice_id 
        // WHERE movie.movie_id=".$f3->get('PARAMS.movieid')." GROUP BY invoice.invoice_id";

        $movie_rental_query = "SELECT * FROM bill
        JOIN rental ON rental.transaction_id=bill.transaction_id
        JOIN inventory ON inventory.inventory_id=rental.inventory_id
        JOIN movie ON inventory.movie_id=movie.movie_id
        JOIN invoice ON invoice.invoice_id=bill.invoice_id
        WHERE movie.movie_id=".$f3->get('PARAMS.movieid');

        $movie_rental_instances = $f3->get('db')->exec($movie_rental_query);

        $f3->set('found', true);

        $total_rentals = 0;
        $total_rental_fees = 0;
        foreach($movie_rental_instances as $result){
            $total_rentals += $result['payment_amount'];
            $total_fees += $result['fees'];
        }

        $f3->set('total_rentals', $total_rentals);
        $f3->set('total_rental_fees', $total_rental_fees);

        //Gather all purchase information for movie
        $purchase_invoices = [];
        // $movie_purchase_query = "SELECT * FROM movie 
        // INNER JOIN inventory ON movie.movie_id=inventory.movie_id 
        // INNER JOIN purchase ON purchase.inventory_id=purchase.inventory_id 
        // INNER JOIN bill ON bill.transaction_id=purchase.transaction_id 
        // INNER JOIN invoice ON invoice.invoice_id=bill.invoice_id 
        // WHERE movie.movie_id=".$f3->get('PARAMS.movieid')." GROUP BY invoice.invoice_id";

        $movie_purchase_query = "SELECT * FROM bill
        JOIN purchase ON purchase.transaction_id=bill.transaction_id
        JOIN inventory ON inventory.inventory_id=purchase.inventory_id
        JOIN movie ON inventory.movie_id=movie.movie_id
        JOIN invoice ON invoice.invoice_id=bill.invoice_id
        WHERE movie.movie_id=".$f3->get('PARAMS.movieid');

        $movie_purchase_instances = $f3->get('db')->exec($movie_purchase_query);
        
        $total_purchases = 0;
        $movies_purchased = [];
        foreach($movie_purchase_instances as $movie){
            $total_purchases += $movie['payment_amount'];
        }
        $f3->set('total_purchases', $total_purchases);
        $totals_rental_purchase = $total_purchases + $total_rentals + $total_rental_fees;
        $f3->set('totals_rental_purchase', $totals_rental_purchase);
        $f3->set('content', 'templates/reports_title.htm');

        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('POST /admin/reports/title/query', 
    function($f3){
        verify_login($f3);
        verify_admin($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        $f3->set('report_search', true);
        $f3->set('page_title', 'Search Title');

        //TODO: Changed column names in actor table to be compatible with multiple join on movies
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
                    ON actor.actor_id = movie_actor.actor_actor_id
                GROUP BY movie.movie_id";
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

        $f3->set('found', count($filtered_movies)>0);

        //Query genres here
        $f3->set('genres',$f3->get('db')->exec('SELECT * FROM genre'));

        //Set movies in view
        $f3->set('movies', $filtered_movies);

        //Display content
        $f3->set('content', 'templates/reports_title.htm');  
        echo \Template::instance()->render('templates/master.htm');

    }
);


$f3->route('GET /admin/reports/genre', 
    function($f3){
        verify_login($f3);
        verify_admin($f3);

        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        $f3->set('page_title', 'Genres Report');

        $f3->set('content', 'templates/reports_genre.htm');

        $f3->set('today', Date('Y-m-d'));

        //Query genres here
        $f3->set('genres',$f3->get('db')->exec('SELECT * FROM genre'));

        echo \Template::instance()->render('templates/master.htm');
    }
);

function divide_by_week($rentals, $purchases, $start_date, $end_date){
    $interval = new DateInterval('P1W');
    $dateRange = new DatePeriod($start_date, $interval, $end_date);

    $weeks = array();
    foreach ($dateRange as $date) { 
        $weeks[$date->format('Y-m-d')]['rentals'] = array_filter($rentals, function($value) use($date){
            $value_date = $value['rental_datetime'];
            $value_date = new DateTime($value_date);
            $date_limit = $date;

            $cweek = $value_date->format('W');
            $dlimitweek = $date_limit->format('W');

            $cyear = $value_date->format('Y');
            $dlimityear = $date_limit->format('Y');

            return ($cweek == $dlimitweek) && ($cyear == $dlimityear);
        });
    }

    foreach($weeks as $week_date=>$values){
        $weeks[$week_date]['purchases'] = array_filter($purchases, function($value) use($week_date){
            $value_date = $value['purchase_datetime'];
            $value_date = new DateTime($value_date);
            $date_limit = new DateTime($week_date);

            $cweek = $value_date->format('W');
            $dlimitweek = $date_limit->format('W');

            $cyear = $value_date->format('Y');
            $dlimityear = $date_limit->format('Y');

            return ($cweek == $dlimitweek) && ($cyear == $dlimityear);
        });
    }
    
    foreach($weeks as $week_key=>$value){
        $weekly_rental_total = 0;
        foreach($value['rentals'] as $rental){
            $weekly_rental_total += $rental['payment_amount'];
        }

        $weekly_purchase_total = 0;
        foreach($value['purchases'] as $purchase){
            $weekly_purchase_total += $purchase['payment_amount'];
        }

        $weeks[$week_key] = [];
        $weeks[$week_key]['rentals']['rental_sum'] = $weekly_rental_total;
        $weeks[$week_key]['purchases']['purchase_sum'] = $weekly_purchase_total;
        $weeks[$week_key]['total'] = $weekly_rental_total + $weekly_purchase_total;
    }
    
    return array_reverse($weeks);
}

function divide_by_month($rentals, $purchases, $start_date, $end_date){
    $interval = new DateInterval('P1M');
    $month = $start_date->format('m');
    $year = $start_date->format('Y');
    $start_date = new DateTime($year."-".$month."-01");
    $end_date = new DateTime($end_date->format('Y-m-t'));
    $dateRange = new DatePeriod($start_date, $interval, $end_date);

    //Generate an array, keyed by date
    $months = array();
    foreach ($dateRange as $date) { 
        $months[$date->format('Y-m-d')]['rentals'] = array_filter($rentals, function($value) use($date){
            $value_date = $value['rental_datetime'];
            $value_date = new DateTime($value_date);
            $date_limit = $date;

            $cmonth = $value_date->format('m');
            $dlimitmonth = $date_limit->format('m');

            return $cmonth == $dlimitmonth;
        });
    }

    //Place values into array
    foreach($months as $month_date=>$values){
        $months[$month_date]['purchases'] = array_filter($purchases, function($value) use($month_date){
            $value_date = $value['purchase_datetime'];
            $value_date = new DateTime($value_date);
            $date_limit = new DateTime($month_date);

            $cmonth = $value_date->format('m');
            $dlimitmonth = $date_limit->format('m');

            return $cmonth == $dlimitmonth;
        });
    }

    foreach($months as $month_key=>$value){
        $monthly_rental_total = 0;
        $monthly_purchase_total = 0;
        foreach($value['rentals'] as $rental){
            $monthly_rental_total += $rental['payment_amount'];
        }

        $monthly_purchase_total = 0;
        foreach($value['purchases'] as $purchase){
            $monthly_purchase_total += $purchase['payment_amount'];
        }


        $months[$month_key] = [];
        $months[$month_key]['rentals']['rental_sum'] = $monthly_rental_total;
        $months[$month_key]['purchases']['purchase_sum'] = $monthly_purchase_total;
        $months[$month_key]['total'] = $monthly_rental_total + $monthly_purchase_total;
    }
    return array_reverse($months);
}

function divide_by_year($rentals, $purchases, $start_date, $end_date){
    $interval = new DateInterval('P1Y');
    $year = $start_date->format('Y');
    $start_date = new DateTime($year."-01-01");
    $dateRange = new DatePeriod($start_date, $interval, $end_date);

    $years = array();
    foreach ($dateRange as $date) { 
        $years[$date->format('Y-m-d')]['rentals'] = array_filter($rentals, function($value) use($date){
            $value_date = $value['rental_datetime'];
            $value_date = new DateTime($value_date);
            $date_limit = $date;

            $cyear = $value_date->format('Y');
            $dlimityear = $date_limit->format('Y');

            return $cyear == $dlimityear;
        });
    }
    foreach($years as $year_date=>$values){
        $years[$year_date]['purchases'] = array_filter($purchases, function($value) use($year_date){
            $value_date = $value['purchase_datetime'];
            $value_date = new DateTime($value_date);
            $date_limit = new DateTime($year_date);

            $cyear = $value_date->format('Y');
            $dlimityear = $date_limit->format('Y');

            return $cyear == $dlimityear;
        });
    }
    foreach($years as $year_key=>$value){
        $yearly_rental_total = 0;
        foreach($value['rentals'] as $rental){
            $yearly_rental_total += $rental['payment_amount'];
        }

        $yearly_purchase_total = 0;
        foreach($value['purchases'] as $purchase){
            $yearly_purchase_total += $purchase['payment_amount'];
        }

        $years[$year_key] = [];
        $years[$year_key]['rentals']['rental_sum'] = $yearly_rental_total;
        $years[$year_key]['purchases']['purchase_sum'] = $yearly_purchase_total;
        $years[$year_key]['total'] = $yearly_rental_total + $yearly_purchase_total;
    }
    return array_reverse($years);
}

$f3->route('POST /admin/reports/genre', 
    function($f3){
        verify_login($f3);
        verify_admin($f3);

        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        $f3->set('page_title', 'Genre Reports');
        
        $interval = $_POST['opttimetype'];
        $genreid = $_POST['movie_genre'];
        $from_date = $_POST['from_date'];
        $to_date = $_POST['to_date'];


        //Gather all rental invoices for the genre
        $rental_invoices = [];
        // $movie_rental_query = "SELECT * FROM movie 
        // JOIN inventory ON movie.movie_id=inventory.movie_id 
        // JOIN rental ON rental.inventory_id=inventory.inventory_id 
        // JOIN bill ON bill.transaction_id=rental.transaction_id 
        // JOIN invoice ON invoice.invoice_id=bill.invoice_id
        // WHERE movie.genre_id=".$genreid;
        $movie_rental_query = "SELECT * FROM bill
        JOIN rental ON rental.transaction_id=bill.transaction_id
        JOIN inventory ON inventory.inventory_id=rental.inventory_id
        JOIN movie ON inventory.movie_id=movie.movie_id
        JOIN invoice ON invoice.invoice_id=bill.invoice_id
        WHERE movie.genre_id=".$genreid;

        if(trim($from_date) != ""){
            $compare = new DateTime($from_date);
            $compare = $compare->format('Y-m-d H:i:s');
            $movie_rental_query .=" AND rental.rental_datetime>'".$compare."'";
        }

        if(trim($to_date) != ""){
            $compare = new DateTime($to_date);
            $compare = $compare->format('Y-m-t');
            $movie_rental_query .=" AND rental.rental_datetime<='".$compare."'";
        }

        $movie_rental_instances = $f3->get('db')->exec($movie_rental_query);
        $f3->set('movie_title', $movie_rental_instances[0]['title']);

        $total_rentals = 0;
        $total_rental_fees = 0;
        if(!empty($movie_rental_instances)){
            foreach($movie_rental_instances as $movie){
                if(!in_array($movie['invoice_id'], $rental_invoices))
                    array_push($rental_invoices, $movie['invoice_id']);
            }
            $rental_invoices = implode(",", $rental_invoices);
            $rental_invoices_query = "SELECT * FROM invoice WHERE invoice_id IN(".$rental_invoices.")";
            $rental_invoices_result = $f3->get('db')->exec($rental_invoices_query);
            foreach($rental_invoices_result as $result){
                $total_rentals += $result['checkout_total'];
                $total_fees += $result['fees'];
            }
        }


        $f3->set('total_rentals', $total_rentals);
        $f3->set('total_rental_fees', $total_rental_fees);

        //Gather all purchase information for genre
        $purchase_invoices = [];
        // $movie_purchase_query = "SELECT * FROM bill 
        // JOIN purchase ON purchase.transaction_id=bill.transaction_id
        // JOIN inventory ON inventory.inventory_id=purchase.inventory_id
        // JOIN movie ON inventory.movie_id=movie.movie_id
        // WHERE movie.genre_id=".$genreid;

        $movie_purchase_query = "SELECT * FROM bill
        JOIN purchase ON purchase.transaction_id=bill.transaction_id
        JOIN inventory ON inventory.inventory_id=purchase.inventory_id
        JOIN movie ON inventory.movie_id=movie.movie_id
        JOIN invoice ON invoice.invoice_id=bill.invoice_id
        WHERE movie.genre_id=".$genreid;

        if(trim($from_date) != ""){
            $compare = new DateTime($from_date);
            $compare = $compare->format('Y-m-d H:i:s');
            $movie_purchase_query .=" AND purchase.purchase_datetime>'".$compare."'";

        }

        if(trim($to_date) != ""){
            $compare = new DateTime($to_date);
            if($interval == 'weekly'){
                $d_position = $compare->format('N');
                $days = 8-$d_position;
                $compare = $compare->add(new DateInterval("P".$days."D"));
                $compare = $compare->format('Y-m-d H:i:s');
            }else{
                $compare = $compare->format('Y-m-t');
            }
            $movie_purchase_query .=" AND purchase.purchase_datetime<='".$compare."'";
        }
        $movie_purchase_instances = $f3->get('db')->exec($movie_purchase_query);
        $total_purchases = 0;
        if(!empty($movie_purchase_instances)){
            foreach($movie_purchase_instances as $movie){
                if(!in_array($movie['invoice_id'], $purchase_invoices))
                    array_push($purchase_invoices, $movie['invoice_id']);
            }

            $purchase_invoices = implode(",", $purchase_invoices);
            $purchase_invoices_query = "SELECT * FROM invoice WHERE invoice_id IN(".$purchase_invoices.")";

            $purchase_invoices_result = $f3->get('db')->exec($purchase_invoices_query);
            foreach($purchase_invoices_result as $result){
                $total_purchases += $result['checkout_total'];
            }
        }

        switch($interval){
            case 'weekly':
                $to_date = new DateTime($to_date);
                $to_date = $to_date->add(new DateInterval("P1D"));
                $weekly_values = divide_by_week($movie_rental_instances, $movie_purchase_instances, new DateTime($from_date), $to_date);
                $f3->set('data', $weekly_values);
                $f3->set('interval', 'Week');
            break;
            case 'monthly':
                $monthly_values = divide_by_month($movie_rental_instances, $movie_purchase_instances, new DateTime($from_date), new DateTime($to_date));
                $f3->set('data', $monthly_values);
                $f3->set('interval', 'Month');
            break;
            case 'yearly':
                $yearly_values = divide_by_year($movie_rental_instances, $movie_purchase_instances, new DateTime($from_date), new DateTime($to_date));
                $f3->set('data', $yearly_values);
                $f3->set('interval', 'Year');
            break;
        }
        $f3->set('total_purchases', $total_purchases);

        $totals_rental_purchase = $total_purchases + $total_rentals + $total_rental_fees;
        $f3->set('totals_rental_purchase', $totals_rental_purchase);


        $f3->set('admin', $_SESSION['admin']);
        $f3->set('content', 'templates/report_genre.htm');

        $genre = $f3->get('db')->exec("SELECT genre_name FROM genre WHERE genre_id=".$genreid)[0]['genre_name'];
        $f3->set('genre', $genre);

        echo \Template::instance()->render('templates/master.htm');
    }
);


$f3->route('GET /admin/customer', 
    function($f3){
        verify_login($f3);
        $f3->set('outstandings', array());
        $f3->set('admin', $_SESSION['admin']);
        $f3->set('page_title', 'Customer Search');
        $f3->set('content', 'templates/customer.htm');
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/customer/@customerid/resolved', 
    function($f3){
        verify_login($f3);
        verify_admin($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        $f3->set('page_title', 'View Customer');
        $customerid = $f3->get('PARAMS.customerid');
        $customer_query = "SELECT * FROM user WHERE user_id='".$customerid."'";
        $customer = $f3->get('db')->exec($customer_query);
        $customer_email = $customer[0]['email'];
        $f3->set('username', $customer_email);

        $customerid = $customer[0]['user_id'];
        $f3->set('customerid', $customerid);

        $f3->set('content', 'templates/customer.htm');

        $balance = 0;

        $date = new DateTime();
        $date = $date->format('Y-m-d');

        $query_eligible_purchases = "SELECT * FROM bill 
        JOIN transaction ON bill.transaction_id=transaction.transaction_id
        JOIN purchase ON purchase.transaction_id=transaction.transaction_id
        JOIN inventory ON inventory.inventory_id=purchase.inventory_id 
        JOIN movie ON movie.movie_id=inventory.movie_id
        WHERE bill.user_id=".$customerid." AND return_end >'".$date."' 
        AND return_datetime IS NULL";

        $purchases = $f3->get('db')->exec($query_eligible_purchases);
        $f3->set('purchases', $purchases);

        $f3->set('balance', $balance);
        $f3->set('outstandings', array()); 
        $f3->set('rentals', array());

        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('POST /admin/customer', 
    function($f3){
        verify_login($f3);
        verify_admin($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        $f3->set('page_title', 'View Customer');
        $f3->set('content', 'templates/customer.htm');
        
        $customer_email = $_POST['email'];
        $f3->set('username', $customer_email);

        $customer_query = "SELECT * FROM user WHERE email='".$customer_email."'";
        $customer = $f3->get('db')->exec($customer_query);
        if(empty($customer)){
            // $f3->set('username', false);
            $f3->set('outstandings', array());
            $f3->set('rentals', array());
            $f3->set('email', $customer_email);
            $f3->set('found', false);
            // $f3->reroute('/admin/customer');
        }else{
            $f3->set('found', true);
            $customerid = $customer[0]['user_id'];
            $f3->set('customerid', $customerid);

            $outstandings = calculate_user_balance($f3, $customerid);

            $balance = 0;
            foreach($outstandings as $key=>$rental){
                if(trim($rental['fees']) != ""){
                    $balance += $rental['fees'];
                }
                $release_date = $rental['date_released'];
                $date_rented = $rental['rental_datetime'];
                $checked_out_rentals[$key]['rental_period'] = Date("Y-m-d", strtotime($date_rented)) < Date("Y-m-d", strtotime($release_date .' + 60 days')) ? 4 : 5;
                $checked_out_rentals[$key]['formatted_date'] = Date("Y-m-d", strtotime($rental['due_datetime']));
            }

            $date = new DateTime();
            $date = $date->format('Y-m-d');

            $query_eligible_purchases = "SELECT * FROM bill 
            JOIN transaction ON bill.transaction_id=transaction.transaction_id
            JOIN purchase ON purchase.transaction_id=transaction.transaction_id
            JOIN inventory ON inventory.inventory_id=purchase.inventory_id 
            JOIN movie ON movie.movie_id=inventory.movie_id
            WHERE bill.user_id=".$customerid." AND return_end >'".$date."' 
            AND return_datetime IS NULL";


            $purchases = $f3->get('db')->exec($query_eligible_purchases);
            $f3->set('purchases', $purchases);

            $get_checked_out_movies = "SELECT * FROM transaction 
            JOIN rental ON transaction.transaction_id=rental.transaction_id 
            JOIN inventory on rental.inventory_id=inventory.inventory_id 
            JOIN movie on movie.movie_id=inventory.movie_id
            WHERE transaction.user_id=".$customerid." AND rental.current_status=0";
            $checked_out_rentals = $f3->get('db')->exec($get_checked_out_movies);
            foreach($checked_out_rentals as $key=>$rental){
                $release_date = $rental['date_released'];
                $date_rented = $rental['rental_datetime'];
                $checked_out_rentals[$key]['rental_period'] = Date("Y-m-d", strtotime($date_rented)) < Date("Y-m-d", strtotime($release_date .' + 60 days')) ? 4 : 5;
                $checked_out_rentals[$key]['formatted_date'] = Date("Y-m-d", strtotime($rental['due_datetime']));

                $current_date = Date('Y-m-d H:i:s');
                $checked_out_rentals[$key]['late'] = Date('Y-m-d H:i:s', strtotime($current_date)) > Date('Y-m-d H:i:s', strtotime($rental['due_datetime']));
            }

            $f3->set('rentals', $checked_out_rentals);

            $f3->set('balance', $balance);
            $f3->set('outstandings', $outstandings); 
        }

        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /resolve/@customerid', 
    function($f3){
        verify_login($f3);
        verify_admin($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);

        $customerid = $f3->get('PARAMS.customerid');
        $current_date = Date('Y-m-d H:i:s');

        $customer_query = "SELECT * FROM user WHERE user_id='".$customerid."'";
        $customer = $f3->get('db')->exec($customer_query);
        $outstandings = calculate_user_balance($f3, $customerid);

        foreach($outstandings as $outstanding){
            $inventory_id = $outstanding['inventory_id'];

            //Create transaction for return 
            $new_transaction = "INSERT INTO transaction (user_id, transaction_type) VALUES(".$customerid.", 'rental')";
            $result = $f3->get('db')->exec($new_transaction);
            
            //Get transaction id for transaction subtype table
            $latest_transaction_id_query = "SELECT transaction_id FROM transaction WHERE transaction_id = (SELECT MAX(transaction_id) FROM transaction) LIMIT 1";
            $transaction_id = $f3->get('db')->exec(array($new_transaction, $latest_transaction_id_query))[0]['transaction_id'];

            //Generate rental return transaction
            $new_rental_transaction ="INSERT INTO rental (transaction_id,"
                                . "inventory_id, due_datetime, current_status) VALUES(".$transaction_id.", ".$inventory_id.", '".$current_date."', 1)";
            $f3->get('db')->exec($new_rental_transaction);
        }

        //Get outstanding rentals
        $balance_query = "SELECT * FROM transaction 
        JOIN rental ON "
        . "transaction.transaction_id=rental.transaction_id 
        JOIN inventory ON 
        inventory.inventory_id=rental.inventory_id
        JOIN movie ON
        inventory.movie_id=movie.movie_id
        WHERE "
        . "transaction.user_id=".$customerid." "
        . "AND rental.current_status=0";
        $outstanding_rentals = $f3->get('db')->exec($balance_query);
        $invoices = [];

        //Update inventory and bill and collect invoices and total invoice fees
        foreach($outstanding_rentals as $rental){
            $fees = $outstandings[$rental['title']]['fees'];
            $current_date = new DateTime("now");
            $due_date = new DateTime($rental['due_datetime']);
            
            //Only resolve late fees
            if($current_date > $due_date){
                //Update all late items to returned
                $post_rentals_query = "UPDATE rental SET current_status=1 
                WHERE transaction_id=".$rental['transaction_id'];
                $f3->get('db')->exec($post_rentals_query);

                //If less than rental period plus 15 days update inventory
                $interval = $current_date->diff($due_date);
                if($interval->format('%a')<15){
                    $get_inventory_count = "SELECT inventory_count FROM inventory WHERE inventory_id=".$inventory_id;
                    $inventory_count = $f3->get('db')->exec($get_inventory_count)[0]['inventory_count'];
                    $inventory_count++;
                    $update_inventory = "UPDATE inventory SET inventory_count=".$inventory_count." WHERE inventory_id=".$inventory_id;
                    $f3->get('db')->exec($update_inventory);
                }                

                //Add fees to invoice for invoice total update
                $get_invoice_query = "SELECT invoice_id FROM bill WHERE transaction_id=".$rental['transaction_id'];
                $invoice_id = $f3->get('db')->exec($get_invoice_query)[0]['invoice_id'];
                $invoices[$invoice_id]['fees'] += $fees;

                //Update bill for each update
                $return_date = Date('Y-m-d H:i:s');
                $transaction_id = $rental['transaction_id'];
                $update_bill_query = "INSERT INTO bill(employee_id, user_id, transaction_id, payment_date, payment_amount, invoice_id) VALUES(1, ".$customerid.", ".$transaction_id.", '".$return_date."', ".$fees.", ".$invoice_id.")";
                $f3->get('db')->exec($update_bill_query);
            }
        }

        //Update invoices with fee totals
        foreach($invoices as $invoice_id=>$fees){
            $update_invoice_exec = "UPDATE invoice SET balance=0, fees=".$fees['fees'];
            $update_invoice_exec .= " WHERE invoice_id=".$invoice_id;
            $f3->get('db')->exec($update_invoice_exec);
        }

        calculate_user_balance($f3, $customerid);
        $f3->reroute("/admin/customer/");
    }
);

$f3->route('GET /admin/resolve/customer/@customerid', 
    function($f3){
        verify_login($f3);
        verify_admin($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);

        $customerid = $f3->get('PARAMS.customerid');
        $current_date = Date('Y-m-d H:i:s');

        $customer_query = "SELECT * FROM user WHERE user_id='".$customerid."'";
        $customer = $f3->get('db')->exec($customer_query);
        $outstandings = calculate_user_balance($f3, $customerid);

        foreach($outstandings as $outstanding){
            $inventory_id = $outstanding['inventory_id'];

            //Create transaction for return 
            $new_transaction = "INSERT INTO transaction (user_id, transaction_type) VALUES(".$customerid.", 'rental')";
            $result = $f3->get('db')->exec($new_transaction);
            
            //Get transaction id for transaction subtype table
            $latest_transaction_id_query = "SELECT transaction_id FROM transaction WHERE transaction_id = (SELECT MAX(transaction_id) FROM transaction) LIMIT 1";
            $transaction_id = $f3->get('db')->exec(array($new_transaction, $latest_transaction_id_query))[0]['transaction_id'];

            //Generate rental return transaction
            $new_rental_transaction ="INSERT INTO rental (transaction_id,"
                                . "inventory_id, due_datetime, current_status) VALUES(".$transaction_id.", ".$inventory_id.", '".$current_date."', 1)";
            $f3->get('db')->exec($new_rental_transaction);
        }

        //Get outstanding rentals
        $balance_query = "SELECT * FROM transaction 
        JOIN rental ON "
        . "transaction.transaction_id=rental.transaction_id 
        JOIN inventory ON 
        inventory.inventory_id=rental.inventory_id
        JOIN movie ON
        inventory.movie_id=movie.movie_id
        WHERE "
        . "transaction.user_id=".$customerid." "
        . "AND rental.current_status=0";
        $outstanding_rentals = $f3->get('db')->exec($balance_query);
        $invoices = [];

        //Update inventory and bill and collect invoices and total invoice fees
        foreach($outstanding_rentals as $rental){
            $fees = $outstandings[$rental['title']]['fees'];
            $current_date = new DateTime("now");
            $due_date = new DateTime($rental['due_datetime']);
            
            //Only resolve late fees
            if($current_date > $due_date){
                //Update all late items to returned
                $post_rentals_query = "UPDATE rental SET current_status=1 
                WHERE transaction_id=".$rental['transaction_id'];
                $f3->get('db')->exec($post_rentals_query);

                //If less than rental period plus 15 days update inventory
                $interval = $current_date->diff($due_date);
                if($interval->format('%a')<15){
                    $get_inventory_count = "SELECT inventory_count FROM inventory WHERE inventory_id=".$inventory_id;
                    $inventory_count = $f3->get('db')->exec($get_inventory_count)[0]['inventory_count'];
                    $inventory_count++;
                    $update_inventory = "UPDATE inventory SET inventory_count=".$inventory_count." WHERE inventory_id=".$inventory_id;
                    $f3->get('db')->exec($update_inventory);
                }                

                //Add fees to invoice for invoice total update
                $get_invoice_query = "SELECT invoice_id FROM bill WHERE transaction_id=".$rental['transaction_id'];
                $invoice_id = $f3->get('db')->exec($get_invoice_query)[0]['invoice_id'];
                $invoices[$invoice_id]['fees'] += $fees;

                //Update bill for each update
                $return_date = Date('Y-m-d H:i:s');
                $transaction_id = $rental['transaction_id'];
                $update_bill_query = "INSERT INTO bill(employee_id, user_id, transaction_id, payment_date, payment_amount, invoice_id) VALUES(1, ".$customerid.", ".$transaction_id.", '".$return_date."', ".$fees.", ".$invoice_id.")";
                $f3->get('db')->exec($update_bill_query);
            }
        }

        //Update invoices with fee totals
        foreach($invoices as $invoice_id=>$fees){
            $update_invoice_exec = "UPDATE invoice SET balance=0, fees=".$fees['fees'];
            $update_invoice_exec .= " WHERE invoice_id=".$invoice_id;
            $f3->get('db')->exec($update_invoice_exec);
        }

        calculate_user_balance($f3, $customerid);
        $route = "/admin/customer/".$customerid."/resolved";
        $f3->reroute($route);
    }
);

$f3->route('GET /admin/return_purchase/@billid', 
    function($f3){
        verify_login($f3);
        verify_admin($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);

        $billid = $f3->get('PARAMS.billid');

        $date = new DateTime();
        $date = $date->format('Y-m-d');

        $query_return = "SELECT * FROM bill 
        INNER JOIN transaction ON bill.transaction_id=transaction.transaction_id
        INNER JOIN purchase ON purchase.transaction_id=transaction.transaction_id
        INNER JOIN inventory ON inventory.inventory_id=purchase.inventory_id 
        INNER JOIN movie ON movie.movie_id=inventory.movie_id
        INNER JOIN invoice ON bill.invoice_id=invoice.invoice_id
        WHERE bill.bill_id=".$billid;

        $purchase = $f3->get('db')->exec($query_return)[0];

        $transactionid = $purchase['transaction_id'];
        $invoiceid = $purchase['invoice_id'];
        $userid = $purchase['user_id'];
        $get_email = "SELECT email FROM user WHERE user_id=".$userid;
        $email = $f3->get('db')->exec($get_email);

        //Update inventory to reflect movie back in stock
        $get_inventory_count = "SELECT inventory_count FROM inventory WHERE inventory.inventory_id=".$purchase['inventory_id'];
        $inventory_count = $f3->get('db')->exec($get_inventory_count)[0]['inventory_count'];
        $inventory_count++;
        $update_inventory = "UPDATE inventory SET inventory_count=".$inventory_count." 
        WHERE inventory_id=".$purchase['inventory_id'];
        $inv_updated = $f3->get('db')->exec($update_inventory);

        //Update purchase table to reflect return date
        $update_purchase = "UPDATE purchase SET return_datetime='".$date."' 
        WHERE purchase.transaction_id=".$transactionid;
        $f3->get('db')->exec($update_purchase);

        //Update invoice minus purchase price
        $updated_total = $purchase['checkout_total'] - $purchase['payment_amount'];
        $update_invoice = "UPDATE invoice SET checkout_total=".$updated_total.".00 WHERE invoice_id=".$invoiceid;
        $updated_invoice = $f3->get('db')->exec($update_invoice);

        //Create a new bill with same transaction id
        $generate_bill = "INSERT INTO bill(transaction_id, user_id, employee_id, payment_date, payment_amount, invoice_id) VALUES(".$transactionid.", ".$userid.", 1, '".$date."', -".$purchase['payment_amount'].", ".$invoiceid.")";
        $generated = $f3->get('db')->exec($generate_bill);



        $f3->reroute('/admin/customer');
        
    }
);

$f3->route('GET /admin/@adminid/pricing', 
    function($f3){
        verify_login($f3);
        verify_admin($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);

        $f3->set('content', 'templates/pricing.htm');
        $f3->set('page_title', 'Pricing');

        $get_prices = "SELECT * FROM pricing";
        $prices = $f3->get('db')->exec($get_prices);

        $costs = [];
        foreach($prices as $price){
            $cost[$price['name']] = $price['price'];
        }
        $f3->set('new_release_price', $cost['new_release']);
        $f3->set('standard_price', $cost['standard']);

        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('POST /admin/@adminid/pricing', 
    function($f3){
        verify_login($f3);
        verify_admin($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        $f3->set('page_title', 'Pricing');

        $standard = $_POST['standard'];
        $new_release = $_POST['new_release'];

        //This for keeping display values appropriate
        $set_standard_price = "UPDATE pricing SET price=".$standard." WHERE name='standard'";
        $set_new_release_price = "UPDATE pricing SET price=".$new_release." WHERE name='new_release'";

        //TODO: TEST THIS!!!!
        //Mass update standard and new release rental prices
        $standard = $standard/5;
        $new_release = $new_release/4;

        $set_standard_movie_price = "UPDATE movie SET
        digital_rental=".$standard.",
        vhs_rental=".$standard.",
        dvd_rental=".$standard.",
        bluray_rental=".$standard;

        $set_new_release_movie_price = "UPDATE movie SET
        new_release_digital_rental=".$new_release.",
        new_release_vhs_rental=".$new_release.",
        new_release_dvd_rental=".$new_release.",
        new_release_bluray_rental=".$new_release;

        //Update everything
        if(trim($standard)!= ""){
            $f3->get('db')->exec($set_standard_price);
            $f3->get('db')->exec($set_standard_movie_price);
            $f3->set('standard_price', $_POST['standard']);
        }
        if(trim($new_release)!=""){
            $f3->get('db')->exec($set_new_release_price);
            $f3->get('db')->exec($set_new_release_movie_price);
            $f3->set('new_release_price', $_POST['new_release']);
        }

        // $get_prices = "SELECT * FROM pricing";
        // $prices = $f3->get('db')->exec($get_prices);

        // $costs = [];
        // foreach($prices as $price){
        //     $cost[$price['name']] = $price['price'];
        // }


        $f3->set('content', 'templates/pricing.htm');
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->set('ONERROR',
    function($f3){
        verify_login($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        if($_SESSION['customer']){
            update_cart($f3);
        }
        $f3->set('page_title', 'Page Not Found');
        $f3->set('content', 'templates/error.htm');
        echo \Template::instance()->render('templates/master.htm');
    }
);

//If new release, due date is 4 days. Otherwise, it is 5 days
function calculate_due_date($release_date){
    $current_date = Date('Y-m-d H:i:s');
    return is_new_release($release_date) ? 
    Date('Y-m-d H:i:s', strtotime($current_date." + 4 days")) : Date('Y-m-d H:i:s', strtotime($current_date." + 5 days"));
}

function calculate_purchase_return_date(){
    $current_date = Date('Y-m-d H:i:s');
    return Date('Y-m-d H:i:s', strtotime($current_date." + 30 days"));
}

function is_new_release($release_date){
    return date("Y-m-d") < Date("Y-m-d", strtotime($release_date .' + 60 days'));
}

$f3->route('GET /checkout', 
    function($f3){
        verify_login($f3);
        $f3->set('customer', $_SESSION['customer']);
        if($_SESSION['customer']){
            update_cart($f3);
        }
        $f3->set('admin', $_SESSION['admin']);
        $f3->set('page_title', 'Checkout');
        $invoice_id = '';
        //TODO: Updated inventory type to take 6 characters
        //TODO: Added inventory of all three formats for each movie
        //TODO: Set digital rental/purchase to available - Not performed
        /*Validation
         * Check for 0 balance or no invoice
         *  if balance, no sale
         * Update invoice
         *  If purchase, save purchase date and time - Complete
         *  Save purchase return date - Complete
         *  
         * If rental
         *  Save rental due date -Complete
         *  Save checkout date and time - Complete
         *  Check new release and charge accordingly - Complete
         * 
         * If new release, charge new release amount, otherwise, charge other amount
         */
        if($_SESSION['balance'] == 0){
            /*
             * Generate an invoice to have an id to attach to bills
             * After all transactions are recorded update invoice to take totals
             */
            $new_invoice_statement = "INSERT INTO invoice (user_id) VALUES(".$_SESSION['userid'].")";
            $invoice_id_query = "SELECT invoice_id FROM invoice WHERE invoice_id = (SELECT MAX(invoice_id) FROM invoice) LIMIT 1";
            $f3->get('db')->exec($new_invoice_statement);
            $invoice_id = $f3->get('db')->exec($invoice_id_query)[0]['invoice_id'];
            
            //For cumulative checkout
            $checkout_total = 0;
            foreach($f3->get('cart')->checkout() as $item){
                $checkout_total += $item['amount']; //Update checkout

                //Update inventory if not digital
                if($item['format']!='digital'){
                    $inventory_count = $f3->get('db')->exec("SELECT inventory_count FROM inventory "
                        . "WHERE movie_id=".$item['movieid']." AND inventory_type='".$item['format']."'")[0]['inventory_count'];
                    $inventory_count--;
                    $update_inventory = "UPDATE inventory SET inventory_count=".$inventory_count." WHERE movie_id=".$item['movieid']." AND inventory_type='".$item['format']."'";
                    $f3->get('db')->exec($update_inventory);
                    
                }
                
                //Gathering inventory id for transaction subtype table
                $inventory_id_query = "SELECT inventory_id FROM inventory "
                        . "WHERE movie_id=".$item['movieid']." "
                        . "AND inventory_type='".$item['format']."'";
                $inventory_id = $f3->get('db')->exec($inventory_id_query)
                        [0]['inventory_id'];
                
                //Update transaction table
                $new_transaction = "INSERT INTO transaction (user_id, "
                        . "transaction_type) VALUES("
                        .$_SESSION['userid'].", '"
                        .strtolower($item['purchase_type'])."')";
                //Get transaction id for transaction subtype table
                $latest_transaction_id_query = "SELECT transaction_id FROM transaction WHERE transaction_id = (SELECT MAX(transaction_id) FROM transaction) LIMIT 1";
                $transaction_id = $f3->get('db')->exec(array($new_transaction, $latest_transaction_id_query))[0]['transaction_id'];
            
                
                //Calculate due date
                $release_date_query = "SELECT date_released FROM movie WHERE movie_id=".$item['movieid'];
                $release_date = $f3->get('db')->exec($release_date_query)[0]['date_released'];
                $due_date = calculate_due_date($release_date);
                
                //Update respective transaction subtype tables
                if(strtolower($item['purchase_type'])== 'rental'){
                    //Update rental table
                    $new_rental_transaction ="INSERT INTO rental (transaction_id,"
                            . "inventory_id, due_datetime) VALUES(".$transaction_id.", ".$inventory_id.", '".$due_date."')";
                    $f3->get('db')->exec($new_rental_transaction);
                }else{
                    $return_date = calculate_purchase_return_date();
                    $new_purchase_transaction ="INSERT INTO purchase (transaction_id,"
                            . "inventory_id, return_end) VALUES(".$transaction_id.", ".$inventory_id.", '".$return_date."')";
                    $f3->get('db')->exec($new_purchase_transaction);
                }
                
                //Generate bill for the item
                $new_bill_statement = "INSERT INTO bill (transaction_id, user_id, invoice_id, employee_id, payment_date, payment_amount) VALUES(".$transaction_id.", "
                        .$_SESSION['userid'] .", ".$invoice_id.", 1, '".Date('Y-m-d H:i:s')."', ".$item['amount'].")";
                $f3->get('db')->exec($new_bill_statement);
            }

            //Update invoice
            $update_invoice = "UPDATE invoice SET checkout_total=".$checkout_total
                    ." WHERE invoice_id=".$invoice_id;
            $f3->get('db')->exec($update_invoice);
        }

        $redirect = "/confirm/checkout/".$invoice_id;

        $f3->reroute($redirect);
    }
);

$f3->route('GET /confirm/checkout/@invoiceid', 
    function($f3){
        verify_login($f3);
        $f3->set('customer', $_SESSION['customer']);
        if($_SESSION['customer']){
            update_cart($f3);
        }
        $f3->set('admin', $_SESSION['admin']);
        $invoiceid = $f3->get('PARAMS.invoiceid');
        $f3->set('page_title', 'Confirmation');

        //Get rentals checked out
        $get_invoice_rentals = "SELECT * FROM invoice 
        JOIN bill ON bill.invoice_id=invoice.invoice_id 
        JOIN transaction ON bill.transaction_id=transaction.transaction_id 
        JOIN rental ON rental.transaction_id=transaction.transaction_id 
        JOIN inventory ON rental.inventory_id=inventory.inventory_id 
        JOIN movie ON movie.movie_id=inventory.movie_id 
        WHERE invoice.invoice_id=".$invoiceid;
        $invoice_rentals = $f3->get('db')->exec($get_invoice_rentals);

        $rental_period = 4;
        foreach($invoice_rentals as $key=>$rental){
            $type = $rental['inventory_type']."_rental";
            $type = is_new_release($rental['date_released']) ? "new_release_".$type : $type;

            $date = new DateTime($rental['due_datetime']);
            $date = $date->format('Y-m-d');
            $invoice_rentals[$key]['due'] = $date;
            $invoice_rentals[$key]['daily'] = $rental[$type];
            $rental_period = is_new_release($rental['date_released']) ? "new_release_rental_period" : "rental_period";
            $invoice_rentals[$key]['rental_period'] = $rental[$rental_period];
            $amount = $rental[$type] * $rental[$rental_period];
            $invoice_rentals[$key]['amount'] = $amount;
        }

        //Get movies purchased
        $get_invoice_purchases = "SELECT * FROM invoice 
        JOIN bill ON bill.invoice_id=invoice.invoice_id 
        JOIN transaction ON bill.transaction_id=transaction.transaction_id 
        JOIN purchase ON purchase.transaction_id=transaction.transaction_id 
        JOIN inventory ON purchase.inventory_id=inventory.inventory_id 
        JOIN movie ON movie.movie_id=inventory.movie_id 
        WHERE invoice.invoice_id=".$invoiceid;

        $invoice_purchases = $f3->get('db')->exec($get_invoice_purchases);
        foreach($invoice_purchases as $key=>$purchase){
            $type = $purchase['inventory_type']."_purchase";
            $invoice_purchases[$key]['purchase_cost'] = $purchase[$type];
        }

        $get_invoice_total = "SELECT checkout_total FROM invoice WHERE invoice_id=".$invoiceid;
        $invoice_total = $f3->get('db')->exec($get_invoice_total)[0]['checkout_total'];

        $f3->set('rentals', $invoice_rentals);
        $f3->set('purchases', $invoice_purchases);
        $f3->set('total', $invoice_total);
        $f3->set('content', 'templates/checkout_confirmation.htm');
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /invoices/@userid', 
    function($f3){
        verify_login($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        if($_SESSION['customer']){
            update_cart($f3);
        }
        $userid = $f3->get('PARAMS.userid');
        $f3->set('page_title', 'Customer Invoices');
        $get_invoices = "SELECT invoice.invoice_id, bill.payment_date, invoice.checkout_total FROM invoice 
        JOIN bill ON bill.invoice_id=invoice.invoice_id 
        WHERE bill.user_id=".$userid." 
        GROUP BY invoice.invoice_id 
        ORDER BY bill.payment_date DESC";

        $invoices = $f3->get('db')->exec($get_invoices);
        $f3->set('invoices', $invoices);

        $f3->set('content', 'templates/invoice_list.htm');
        echo \Template::instance()->render('templates/master.htm');        
    }
);

$f3->route('GET /invoice/@invoiceid', 
    function($f3){
        verify_login($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        if($_SESSION['customer']){
            update_cart($f3);
        }
        $invoiceid = $f3->get('PARAMS.invoiceid');
        $f3->set('page_title', 'View Invoice');

        //Get rentals checked out
        $get_invoice_rentals = "SELECT * FROM invoice 
        JOIN bill ON bill.invoice_id=invoice.invoice_id 
        JOIN transaction ON bill.transaction_id=transaction.transaction_id 
        JOIN rental ON rental.transaction_id=transaction.transaction_id 
        JOIN inventory ON rental.inventory_id=inventory.inventory_id 
        JOIN movie ON movie.movie_id=inventory.movie_id 
        WHERE invoice.invoice_id=".$invoiceid;
        $invoice_rentals = $f3->get('db')->exec($get_invoice_rentals);
        foreach($invoice_rentals as $key=>$rental){
            $date = "Not yet returned";
            if($rental['current_status'] == 1){
                $date = new DateTime($rental['return_datetime']);
                $date = $date->format('Y-m-d');
            }
            $invoice_rentals[$key]['return'] = $date;
            $amount = $rental['payment_amount'];
            $invoice_total += $amount;
        }

        //Get movies purchased
        $get_invoice_purchases = "SELECT * FROM invoice 
        JOIN bill ON bill.invoice_id=invoice.invoice_id 
        JOIN transaction ON bill.transaction_id=transaction.transaction_id 
        JOIN purchase ON purchase.transaction_id=transaction.transaction_id 
        JOIN inventory ON purchase.inventory_id=inventory.inventory_id 
        JOIN movie ON movie.movie_id=inventory.movie_id 
        WHERE invoice.invoice_id=".$invoiceid;

        $invoice_purchases = $f3->get('db')->exec($get_invoice_purchases);


        
        $get_invoice = "SELECT * FROM invoice WHERE invoice_id=".$invoiceid;
        $invoice = $f3->get('db')->exec($get_invoice)[0];

        $invoice_total = $invoice['checkout_total'] + $invoice['fees'];

        $f3->set('fees', $invoice['fees']);
        $f3->set('rentals', $invoice_rentals);
        $f3->set('purchases', $invoice_purchases);
        $f3->set('total', $invoice_total);

        $f3->set('content', 'templates/invoice_detail.htm');
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /checkout/@customerid', 
    function($f3){
        verify_login($f3);
        $f3->set('customer', $_SESSION['customer']);
        $f3->set('admin', $_SESSION['admin']);
        $f3->set('cart', $f3->get('cart')); 
        $f3->set('page_title', 'Checkout');

        //Compute total cart cost here
        $items = $f3->get('cart')->find();
        $cart_total = 0;
        foreach($items as $item){
            $cart_total += $item->amount;
        }
        $f3->set('cart_total_cost', $cart_total);  

        $f3->set('content', 'templates/checkout.htm');
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('POST /movies/cart/add/@movieid',
    function($f3){
        verify_login($f3);
        $movieid = $f3->get('PARAMS.movieid');
        $purchase_type = $_POST['buytype'];
        $format = $_POST['format'];
        
        //Check current rentals against rental limit
        if(strtolower($purchase_type)=='rental'){
            //Rentals already in user's possession
            $query_rentals_outstanding = "SELECT COUNT(*) FROM transaction JOIN rental ON "
                    . "transaction.transaction_id=rental.transaction_id WHERE transaction.user_id="
                    .$_SESSION['userid']." AND rental.current_status=0";
            
            //Rentals checked out
            $rentals_outstanding = $f3->get('db')->exec($query_rentals_outstanding)[0]['COUNT(*)'];

            //Rentals in cart
            $cart_rentals = count($f3->get('cart')->find('purchase_type', 'Rental'));
            
            //If user already possesses 2 rentals no more
            if(($cart_rentals + $rentals_outstanding) == 2){
                //Add two rental max notification
                $_SESSION['max_rentals_reached'] = true;
                $f3->reroute("/movies/".$movieid);
            }
        }

        //If user did not exceed rentals
        // add item
        $movie_query = "SELECT * FROM movie WHERE movie_id=".$movieid;
        $movie = $f3->get('db')->exec($movie_query)[0];
        
        $cost_type = strtolower($format)."_".strtolower($purchase_type);

        //This cost should remain unchanged if this is a purchase
        $price = $movie[$cost_type];
        $cost = $price;

        //If movie is a rental, calculate rental period total cost
        if(strtolower($purchase_type) == 'rental'){
            if(is_new_release($movie['date_released'])){
                $cost_type = "new_release_".$cost_type;
                $rental_period = 4;
            } else {
                $rental_period = 5;
            }
            $price = $movie[$cost_type];
            $cost = $price * $rental_period;
        } 

        $f3->get('cart')->set('movieid', $movieid);
        $f3->get('cart')->set('movie_title', $movie['title']);
        $f3->get('cart')->set('amount',$cost);
        $f3->get('cart')->set('format', $format);
        $f3->get('cart')->set('purchase_type',ucfirst($purchase_type));
        $f3->get('cart')->set('cost_type', $cost_type);
        $f3->get('cart')->set('rental_period', $rental_period);
        $f3->get('cart')->save();
        $f3->get('cart')->reset();
        $f3->reroute("/movies/".$movieid);
});

$f3->route('GET /movies/cart/empty', 
    function($f3){
        $f3->get('cart')->drop();
        $f3->reroute('/movies');
    }
);

$f3->route('GET /movies/cart/remove/@movieid', 
    function($f3){
        $movieid = $f3->get('PARAMS.movieid');
        $f3->get('cart')->erase('movieid', $movieid);
        $f3->reroute('/movies');
    }
);

$f3->run();
?>
