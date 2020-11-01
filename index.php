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
            $_SESSION['admin'] = true;
            $_SESSION['customer'] = false;
            // $f3->set('admin', true);
            // $f3->set('customer')
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

function verify_login(){
    if($_SESSION['logged_in']==false){
        $f3->reroute('/login');
    }
}

$f3->route('GET /logout', 
    function($f3){
        $_SESSION['logged_in'] = false;
        $f3->set('page_title', 'Login');
        echo \Template::instance()->render('templates/login.htm');
    }
);

$f3->route('GET /',
    function($f3) {
        verify_login();
        update_cart($f3);
        $f3->reroute('/movies');
    }
);


function calculate_user_balance($f3, $userid){
        $balance_query = "SELECT * FROM transaction JOIN rental ON "
        . "transaction.transaction_id=rental.transaction_id WHERE "
        . "transaction.user_id=".$userid." "
        . "AND rental.current_status=0";
        $outstanding_rentals = $f3->get('db')->exec($balance_query);
        $balance = 0;
        $outstanding_array = [];
        foreach($outstanding_rentals as $rental){
            $current_date = new DateTime("now");
            $due_date = new DateTime($rental['due_datetime']);
            //TODO: On customer side verify invoice is updated with checkout total on checkout
            //TODO: generate a purchase return page - list all purchases and dates and make the proper dates returnable
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
                        $balance += 2;
                    }
                }else{
                    if($days==2){
                        //TODO: Set digital availability to unavailable
                    }
                }
            }
        }
        $_SESSION['balance'] = $balance;
        return $outstanding_array;
        //TODO: Update invoice to reflect current balance
}

$f3->route('GET /movies',
    function($f3) {
        verify_login();
        update_cart($f3);
        calculate_user_balance($f3, $_SESSION['userid']);

        //General page values
        // $f3->set('customer', true);  
        // $f3->set('admin', false);  
    	$f3->set('page_title', 'Movies');

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
        verify_login();
        update_cart($f3);
        
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
        verify_login();
        update_cart($f3);
        // $f3->set('customer', true);

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

function update_cart($f3){
    //Calculate cart rental count
    $get_rentals="SELECT COUNT(*) FROM transaction JOIN rental ON transaction.transaction_id=rental.transaction_id WHERE transaction.user_id=".$_SESSION['userid']." AND rental.current_status=false";
    $number_rentals_outstanding = $f3->get('db')->exec($get_rentals)[0]['COUNT(*)'];
    $total_rentals = $number_rentals_outstanding + $rentals_in_cart;
    $f3->set('rental_count', $total_rentals);
    $rentals_available = 2-$total_rentals;
    if($rentals_available == 0){
        $f3->set('cart_info', 'You have reached your maximum 2 rentals at a time');
    }else{
        $f3->set('cart_info', "You can add ".$rentals_available." rental(s) to your cart");
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
        verify_login();
        update_cart($f3);
    	$f3->set('page_title', 'Movies');   
        // $f3->set('customer', true);
        $movieid = $f3->get('PARAMS.movieid');

    	//retrieve movie from database by id here
        $movie_query = "SELECT * FROM movie JOIN genre ON movie.genre_id=genre.genre_id JOIN director ON movie.director_id = director.director_id WHERE movie_id=".$movieid." ";
        $f3->set('movie', $f3->get('db')->exec($movie_query)[0]);

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
        verify_login();
        update_cart($f3);
        //TODO: Notify password successful
        $f3->set('password_succes', $_SESSION['password_succes']);

        $f3->set('page_title', 'Profile');
        // $f3->set('customer', true);

        //Query user by id and get all related information
        $f3->set('username', ucfirst($_SESSION['username']));

        //Calculate total user debt here
        $f3->set('balance', $_SESSION['balance']);

        //Calculate preferred genre here
        //WHERE user_id='".$_SESSION['userid']."'
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
        $f3->set('content', 'templates/profile.htm');
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin', 
    function($f3){
        verify_login();

        $f3->set('admin', true);
        $f3->set('content', 'templates/admin_home.htm');
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/@movieid/edit', 
    function($f3){
        verify_login();
        
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
            'inventory' => $vhs_movie['inventory_count']
        ));
        $f3->set('dvd', array(
            'rental' => $movie[0]['dvd_rental'],
            'purchase' => $movie[0]['dvd_purchase'],
            'inventory' => $dvd_movie['inventory_count']
        ));
        $f3->set('bluray', array(
            'rental' => $movie[0]['bluray_rental'],
            'purchase' => $movie[0]['bluray_purchase'],
            'inventory' => $bluray_movie['inventory_count']
        ));

        $f3->set('available', $movie[0]['available']);
        $f3->set('movieid', $f3->get('PARAMS.movieid'));

        //TODO: LAST TODO!!! Add delete movie functionality
        $f3->set('title', $movie[0]['title']);
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('POST /admin/@movieid/edit', 
    function($f3){
        verify_login();
        $f3->set('admin', true);
        $f3->set('page_title', 'Title Search'); 
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
                $f3->reroute('/admin/title');
            case "close":
                $f3->reroute('/admin/title');
            break;
            case "delete":
            break;
        }
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/title',
    function($f3) {
        verify_login();
        update_cart($f3);
        // calculate_user_balance($f3. $_SESSION['userid']);

        //General page values
        $f3->set('page_title', 'Title Search');

        //Query genres here
        $f3->set('genres',$f3->get('db')->exec('SELECT * FROM genre'));

        //Initialize movies
        $movies_query ='SELECT * FROM movie JOIN genre ON movie.genre_id=genre.genre_id JOIN director ON movie.director_id = director.director_id';
        $f3->set('movies', $f3->get('db')->exec($movies_query));

        //TODO: Add admin qui differentiation for movie search
        //Display the page
        $f3->set('content', 'templates/movies_list.htm');  
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/title/@movieid',
    function($f3) {
        verify_login();

        $f3->set('admin', $_SESSION['admin']);
        $f3->set('page_title', 'Title Search'); 
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

        //Get inventory information
        $f3->set('vhs', array(
            'rental' => $movie['vhs_rental'],
            'purchase' => $movie['vhs_purchase'],
            'inventory' => $vhs_inventory['inventory_count']
        ));
        $f3->set('dvd', array(
            'rental' => $movie['dvd_rental'],
            'purchase' => $movie['dvd_purchase'],
            'inventory' => $dvd_inventory['inventory_count']
        ));
        $f3->set('bluray', array(
            'rental' => $movie['bluray_rental'],
            'purchase' => $movie['bluray_purchase'],
            'inventory' => $bluray_inventory['inventory_count']
        ));
        $f3->set('digital', array(
            'rental' => $movie['digital_rental'],
            'purchase' => $movie['digital_purchase']
        ));

        $f3->set('available', $movie['available']);
        $f3->set('movieid', $f3->set('movieid', $f3->get('PARAMS.movieid')));
        echo \Template::instance()->render('templates/master.htm');
    }
);

// $f3->route('POST /admin/title',
//     function($f3) {
//         verify_login();

//         $f3->set('admin', true);
//         $f3->set('page_title', 'View Title'); 
//         $f3->set('content', 'templates/title_search.htm'); 


//         //Get inventory information
//         $f3->set('vhs', array(
//             'rental' => '4',
//             'purchase' => '29',
//             'inventory' => '80'
//         ));
//         $f3->set('dvd', array(
//             'rental' => '4',
//             'purchase' => '29',
//             'inventory' => '30'
//         ));
//         $f3->set('bluray', array(
//             'rental' => '4',
//             'purchase' => '50',
//             'inventory' => '20'
//         ));

//         $f3->set('movieid', 1);
//         echo \Template::instance()->render('templates/master.htm');
//     }
// );

$f3->route('GET /admin/reports/title', 
    function($f3){
        verify_login();

        $f3->set('admin', true);
        $f3->set('content', 'templates/reports_title.htm');

        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/reports/genre', 
    function($f3){
        verify_login();

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
        verify_login();

        $f3->set('admin', $_SESSION['admin']);
        $f3->set('content', 'templates/customer.htm');
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('POST /admin/customer', 
    function($f3){
        verify_login();
        $customer_email = $_POST['email'];

        $customer_query = "SELECT * FROM user WHERE email='".$customer_email."'";
        $customer = $f3->get('db')->exec($customer_query);
        $customerid = $customer[0]['user_id'];
        $f3->set('customerid', $customerid);

        $outstandings = calculate_user_balance($f3, $customerid);
        $f3->set('admin', $_SESSION['admin']);
        $f3->set('content', 'templates/customer.htm');

        $balance = 0;
        foreach($outstandings as $movie){
            if(trim($movie['fees']) != ""){
                $balance += $movie['fees'];
            }
        }

        $f3->set('balance', $balance);
        $f3->set('outstandings', $outstandings); 

        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/resolve/customer/@customerid', 
    function($f3){
        verify_login();
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

        $f3->reroute("/admin/customer/");
    }
);

$f3->route('GET /admin/@adminid/pricing', 
    function($f3){
        verify_login();

        $f3->set('admin', true);
        $f3->set('content', 'templates/pricing.htm');

        $f3->set('new_release_price', '4');
        $f3->set('standard_price', '3.50');

        echo \Template::instance()->render('templates/master.htm');
    }
);

// $f3->set('ONERROR',
//     function($f3){
//         $f3->set('page_title', 'Page Not Found');
//         $f3->set('content', 'templates/error.htm');
//         echo \Template::instance()->render('templates/master.htm');
//     }
// );

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
    return date("Y-m-d") < Date("Y-m-d", strtotime($release_date ." + 60 days"));
}

$f3->route('GET /checkout', 
    function($f3){
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
        $invoice_balance = $f3->get('db')->exec("SELECT balance FROM invoice "
                . "WHERE user_id=".$_SESSION['userid'])[0]['balance'];
        if($invoice_balance){
            //TODO: Add balance notify to checkout
            //TODO: Notify user that user has balance
        }else{
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
    }
);

$f3->route('POST /movies/cart/add/@movieid',
    function($f3){
        $movieid = $f3->get('PARAMS.movieid');
        $purchase_type = $_POST['buytype'];
        $format = $_POST['format'];
        
        //Check current rentals against rental limit
        if(strtolower($purchase_type)=='rental'){
            //Rentals already in user's possession
            $query_rentals_outstanding = "SELECT COUNT(*) FROM transaction JOIN rental ON "
                    . "transaction.transaction_id=rental.transaction_id WHERE transaction.user_id="
                    .$_SESSION['userid']." AND rental.current_status=0";
            //Rentals in cart
            $rentals_outstanding = $f3->get('db')->exec($query_rentals_outstanding)[0]['COUNT(*)'];
            
            //If user already possesses 2 rentals no more
            $cart_rentals = count($f3->get('cart')->find('purchase_type', 'Rental'));
            if(($cart_rentals + $rentals_outstanding) >= 2){
                //TODO: Add two rental max notification
                $f3->reroute("/movies/".$movieid);
            }
        }
        
        //If user did not exceed rentals
        // add item
        $movie_query = "SELECT * FROM movie WHERE movie_id=".$movieid;
        $movie = $f3->get('db')->exec($movie_query)[0];
        
        //TODO: notify cart is heavily determinate on movie naming
        $cost_type = strtolower($format)."_".strtolower($purchase_type);
        $cost = $movie[$cost_type];
        if(strtolower($purchase_type) == 'rental'){
            $cost = is_new_release($release_date) ? 4 : 3.50;
        } 
        
        $f3->get('cart')->set('movieid', $movieid);
        $f3->get('cart')->set('movie_title', $movie['title']);
        $f3->get('cart')->set('amount',$cost);
        $f3->get('cart')->set('format', $format);
        $f3->get('cart')->set('purchase_type',ucfirst($purchase_type));
        $f3->get('cart')->set('cost_type', $cost_type);
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
