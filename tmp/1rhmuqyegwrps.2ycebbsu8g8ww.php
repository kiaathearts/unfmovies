<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../css/login.css">    
    <title>Login</title>
</head>
<body class="login-body">
<div class="container">
        <div class="card mx-auto text-light login">
	  <h5 class="card-header" style="background-color: #013F70;">Welcome to UNFMovies!</h5>
	  <div class="card-body">
	  	<?php if ($admin_login): ?>
	  		
	  			<form method="post" action="/admin/login">
	  		
	  		<?php else: ?>
	    		<form method="post" action="/login">
	  		
	  	<?php endif; ?>
	    	<?php if ($admin_login): ?>
	    		
					<p class="text-dark">Admin Login</p>
		    		<input type="text" class="form-control p-1 m-1" placeholder="username" name="username">
	    		
	    		<?php else: ?>
					<p class="text-dark">Customer Login</p>
		    		<input type="email" class="form-control p-1 m-1" placeholder="youremail@email.com" name="email">
	    		
	    	<?php endif; ?>
	    	<input type="password" class="form-control p-1 m-1" placeholder="password" name="password">
	    	<button type="submit" class="btn btn-lg btn-block" style="background-color: #013F70; color: #fff">Login</button>
	    	<?php if ($admin_login): ?>
	    		
	    			<a href="/login" style="color: #013F70"> Go to customer login</a>
	    		
	    		<?php else: ?>
	    			<a href="/admin/login" style="color: #013F70">Go to admin login</a>
	    		
	    	<?php endif; ?>

	    </form>
	</div>
	</div>
	</div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>
