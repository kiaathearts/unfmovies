<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand text-primary" href="/">UNFMovies</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <!-- <button type="button" class="btn">Sign Out</button> -->
    <a class="btn btn-primary" href="/logout" role="button">Sign Out</a>
    <?php if ($customer): ?>
    	<a class="nav-link" href="/profile/<?= ($SESSION['userid']) ?>">My Profile</a>
    <?php endif; ?>
    <?php if ($admin): ?>
      <a class="nav-link" href="/admin/title">Title</a>      
      <a class="nav-link" href="/admin/customer">Customer</a>      
      <a class="nav-link" href="/admin/reports/title">Reports by Title</a>      
      <a class="nav-link" href="/admin/reports/genre">Reports by Genre</a>      
      <a class="nav-link" href="/admin/<?= ($userid) ?>/pricing">Pricing</a>      
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
      			</div>
      			<div class="col-lg-5 col-sm-5 col-5 total-section text-right">
      				<p>Total: <span class="text-info">$<?= ($cart_total_cost) ?></span></p>
      			</div>
      	 </div>
    	<!--------------------- TODO: Add real functionality here------------------->
        <?php foreach (($cart->find()?:[]) as $cart_item): ?>
          <div class="row cart-detail">
            <div class="col-lg-8 col-sm-8 col-8 cart-detail-product">
              <a href="/movies/<?= ($cart_item->movieid) ?>"><p><?= ($cart_item->movie_title) ?></p></a>
              <span class="price text-info"> $<?= ($cart_item->amount) ?></span> <span class="count"> <?= ($cart_item->purchase_type) ?></span>
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
              <!-- <button class="cart-btn btn-primary btn-block">Checkout</button> -->
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
