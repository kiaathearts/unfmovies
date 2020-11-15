<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <?php if ($admin): ?>
    
        <a class="navbar-brand" href="/admin">
          <img src="images/UNF_Logo_SM.jpg" alt="">
        </a>
      <a class="navbar-brand text-primary" href="/admin">UNFMovies</a>
    
    <?php else: ?>
<!--       <a class="navbar-brand" href="/">
          <img src="images/UNF_Logo_SM.jpg" alt="">
        </a> -->
      <a class="navbar-brand text-primary" href="/">UNFMovies</a>
    
  <?php endif; ?>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <a class="btn btn-primary" href="/logout" role="button">Sign Out</a>
    <?php if ($customer): ?>
    	<a class="nav-link" href="/profile/<?= ($SESSION['userid']) ?>">My Profile</a>
      <a class="nav-link" href="/movies">Search</a>
      <a class="nav-link" href="/checkout/<?= ($SESSION['userid']) ?>">Checkout</a>
      <a class="nav-link" href="/profile/<?= ($SESSION['userid']) ?>">Return</a>
      <a class="nav-link" href="/profile/<?= ($SESSION['userid']) ?>">Check balance/Pay fees</a>
      <a class="nav-link" href="/invoices/<?= ($SESSION['userid']) ?>">View Invoices</a>
      <a class="nav-link" href="/review/<?= ($SESSION['userid']) ?>">Write review(with score)</a>
    <?php endif; ?>
    <?php if ($admin): ?>
      <a class="nav-link" href="/admin/title">Title</a>      
      <a class="nav-link" href="/admin/customer">Customer</a>      
      <a class="nav-link" href="/admin/reports/title">Reports by Title</a>      
      <a class="nav-link" href="/admin/reports/genre">Reports by Genre</a>      
      <a class="nav-link" href="/admin/<?= ($SESSION['employee_id']) ?>/pricing">Pricing</a>      
    <?php endif; ?>
  </div>
    <?php if ($customer): ?>
    	<div class="dropdown">
        <button type="button" class="btn btn-info" data-toggle="dropdown">
          <i class="fa fa-shopping-cart" aria-hidden="true"></i> Cart <span class="badge badge-pill badge-danger"><?= ($cart->count()) ?></span>
        </button>
        <div class="dropdown-menu">
        	<div class="row total-header-section">
      			<div class="col-lg-6 col-sm-5 col-5">
      				<i class="fa fa-shopping-cart" aria-hidden="true"></i> <span class="badge badge-pill badge-danger"><?= ($cart->count())."
" ?>
              </span>
              <?php if ($SESSION['balance'] > 0): ?>
                <p>You have a balance of <?= ($balance) ?>, that must be paid before you can make any more purchases or checkout rentals. Please, visit your profile and resolve your balance.</p>
              <?php endif; ?>
      			</div>
      			<div class="col-lg-5 col-sm-5 col-5 total-section text-right">
      				<p>Total: <span class="text-info">$<?= ($cart_total_cost) ?></span></p>
      			</div>
      	 </div>
        <?php foreach (($cart->find()?:[]) as $cart_item): ?>
          <div class="row cart-detail">
            <div class="col-lg-8 col-sm-8 col-8 cart-detail-product">
              <a href="/movies/<?= ($cart_item->movieid) ?>"><p><?= ($cart_item->movie_title) ?></p></a>
              <span class="price text-info"> $<?= ($cart_item->amount) ?> - 
                <?php if ($cart_item->rental_period): ?>
                  
                    <?= ($cart_item->rental_period) ?> Day Rental
                    
                  
                  <?php else: ?>
                    Purchase
                  
                <?php endif; ?>
              </span> 
            </div>
            <div class="col-lg-1 col-sm-1 col-1">
              <a type="button" href="/movies/cart/remove/<?= ($cart_item->movieid) ?>" class="btn btn-link">
               <i class="fa fa-trash" aria-hidden="true"></i>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if ($cart->count() > 0): ?>
          <div class="row">
            <div class="col-lg-12 col-sm-12 col-12 text-center checkout">
              <span class="align-text-bottom"><a class="cart-btn btn-primary btn-block" href="/checkout">Checkout</a></span>
            </div>
          </div>
          <div class="row total-header-section">
              <a href="/movies/cart/empty" class="cart-btn">Empty Cart</a>
          </div>
        <?php endif; ?>
        <?php if ($cart->count() == 0): ?>
            <div class="col-lg-12 col-sm-12 col-12 cart-detail-product">
              <h5>You don't have anything in your cart, yet!</h5>
            </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
</nav>

  <div>
    <?php if (@$_SESSION['max_rentals_reached']): ?>
      
        <a href="/profile/<?= ($SESSION['userid']) ?>">Before you are able to rent any more movies, you will need to remove a rental from your basket or return any rentals you have checked out</a>
      
    <?php endif; ?>

  </div>
