<nav class="navbar navbar-expand-lg">
  <?php if ($admin): ?>
    
      <a class="navbar-brand" style="width:150px; height: 105px; overflow: hidden; display:block; padding: 0; margin: -1% 12px -1% -1%;" href="/admin">
        <img src="/images/unfmoviesangle.png" style="width: 70%; height: auto; margin: 4% 0 0 0;" alt="">
      </a>
    
    <?php else: ?>
      <a class="navbar-brand" style="width:150px; height: 105px; overflow: hidden; display:block; padding: 0; margin: -1% 12px -1% -1%;" href="/">
          <img src="/images/unfmoviesangle.png" style="width: 70%; height: auto; margin: 4% 0 0 0;" alt="">
        </a>
    
  <?php endif; ?>
  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <?php if ($admin): ?>
      
        <a class="nav-link" href="/admin/title">Locate Title/Update Inventory</a> |
        <a class="nav-link" href="/admin/movie/add">Add New Movie</a> |     
        <a class="nav-link" href="/admin/customer">Check Customer Balance</a> |      
        <a class="nav-link" href="/admin/reports/title">Reports by Title</a> |     
        <a class="nav-link" href="/admin/reports/genre">Reports by Genre</a> |     
        <a class="nav-link" href="/admin/<?= ($SESSION['employee_id']) ?>/pricing">Adjust Pricing</a> |   
      
      <?php else: ?>
        <a class="nav-link" href="/">Movies</a> |
        <a class="nav-link" href="/profile/<?= ($SESSION['userid']) ?>">Return Rentals</a> |     
        <a class="nav-link" href="/profile/<?= ($SESSION['userid']) ?>">Check and <br/> Pay Balance</a> |     
        <a class="nav-link" href="/invoices/<?= ($SESSION['userid']) ?>">Invoices</a>      
      
    <?php endif; ?>
  </div>
    <?php if ($customer): ?>
      <ul style="list-style-type: none; margin-top: 1%;">
          <li class="nav-item dropdown">
            <a class="nav-link" href="/profile/<?= ($SESSION['userid']) ?>">Hello, <?= (ucfirst($SESSION['first_name'])) ?><br/><span style="margin-left: 35px">Profile</span></a>
            <div class="dropdown-menu">
            </div>
        </li>
      </ul>
        
      <!---------------------------------------Cart------------------------------------------->
    	<div class="dropdown">
        <button type="button" class="btn nav-cart-btn" data-toggle="dropdown">
          <i class="fa fa-shopping-cart" aria-hidden="true"></i> Cart <span class="badge badge-pill badge-primary"><?= ($cart->count()) ?></span>
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
            <div class="col col-12">
              <a href="/movies/cart/empty" >Empty Cart</a>
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
              <a class="btn btn-block" style="background-color: #007bff; color: #fff" href="/checkout/<?= ($SESSION['userid']) ?>">Proceed to Checkout</a>
            </div>
          </div>
        <?php endif; ?>
        <?php if ($cart->count() == 0): ?>
            <div class="col-lg-12 col-sm-12 col-12 cart-detail-product">
              <h5>You don't have anything in your cart, yet!</h5>
            </div>
        <?php endif; ?>
      </div>
    </div>
    <!---------------------------------------/Cart------------------------------------------->
     
    <?php endif; ?>
    <a class="nav-link" href="/logout">Sign Out</a>
</nav>

<div>
  <?php if (@$_SESSION['max_rentals_reached']): ?>
    
      <a href="/profile/<?= ($SESSION['userid']) ?>">Before you are able to rent any more movies, you will need to remove a rental from your basket or return any rentals you have checked out</a>
    
  <?php endif; ?>
</div>
