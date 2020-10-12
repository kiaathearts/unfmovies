<?php
//added apps: Fatfree Framework
//Allow All on apache2.conf.save on Directory
//Allow all on apache2 000-default.conf
//create htaccess file from f3 
//enable ssl on php.ini - dev and prod and standard


 if(!$_SERVER['REMOTE_ADDR']=='::1'){
    require 'vendor/autoload.php';
 } else {
    require '/home/kia/vendor/autoload.php';
}  


$f3 = \Base::instance();
$f3->set('head', 'templates/head.htm');
$f3->set('navbar', 'templates/navbar.htm');
$f3->set('footscripts', 'templates/footscripts.htm'); 
$f3->set('footer', 'templates/footer.htm');

//Get current user id here
$f3->set('userid', 1);

$f3->route('GET /movies',
    function($f3) {
        //Query genres here
        $f3->set('genres', array('Horror', 'Action', 'Suspense', 'Romance', 'Sci-Fi', 'Drama'));
        $f3->set('cart_total_cost', '8.00');
        $f3->set('cart_count', 4);
        $f3->set('customer', true);  
    	$f3->set('page_title', 'Movies');   
    	$f3->set('content', 'templates/movies_list.htm');  
		echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /movies/@movieid',
    function($f3) {
        $f3->set('customer', true);

    	//retrieve movie from database by id here
    	$f3->set('movie_title', 'Attack on Titan'); 
    	$f3->set('director', 'Jill Scott'); 
    	$f3->set('actors', array('Liza Minelly', 'Ruth Anne', 'Amp Rutherfor')); 
    	$f3->set('synopsis', 'A magician finds real magic when he discovers the terrifying secret his lover has. Will he save her and live happily ever after or are they both doomed to another terrifying remake of a DC/Marvel film?'); 
    	$f3->set('release_year', '1989'); 
    	$f3->set('page_title', 'Movies');   

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
        $f3->set('customer', true);

        //Cart info for cart
        $f3->set('cart_total_cost', '8.00');
        $f3->set('cart_count', 4);

        //Query user by id and get all related information
        $f3->set('username', 'BrookeBook');
        $f3->set('page_title', 'Profile');

        //Calculate total user debt here
        $f3->set('total_cost', '8.00');

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
    });

$f3->route('GET /login',
    function($f3) {
    	$f3->set('page_title', 'Login');           
		echo \Template::instance()->render('login.php');
    }
);

$f3->route('GET /admin', 
    function($f3){
        $f3->set('admin', true);
        $f3->set('content', 'templates/admin_home.htm');
        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/@movieid/edit', 
    function($f3){
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
        $f3->set('admin', true);
        $f3->set('content', 'templates/reports_title.htm');

        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/reports/genre', 
    function($f3){
        $f3->set('admin', true);
        $f3->set('content', 'templates/reports_genre.htm');

        //Query genres here
        $f3->set('genres', array('Horror', 'Action', 'Suspense', 'Romance', 'Sci-Fi', 'Drama'));

        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/reports/@genreid/@interval', 
    function($f3){
        $f3->set('admin', true);
        $f3->set('content', 'templates/report_genre.htm');

        $f3->set('genre', 'Horror');
        $f3->set('interval', 'Week');

        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->route('GET /admin/customer', 
    function($f3){
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
        $f3->set('admin', true);
        $f3->set('content', 'templates/pricing.htm');

        $f3->set('new_release_price', '4');
        $f3->set('standard_price', '3.50');

        echo \Template::instance()->render('templates/master.htm');
    }
);

$f3->run();
?>
