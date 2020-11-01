<div class="container">
	<!---------------This is where the movie image should display-------------->
	<div class="jumbotron jumbotron-fluid"></div>
	<div class="row mb-2">
		<div class="col offset-md-1">
			<p class="display-2"><?= ($movie['title']) ?></p>
		</div>
	</div>
	<!--------------------MOVIE DATA-------------------------------->
	<div class="row">
		<div class="col offset-md-1">
			<h4>Available in <?= ($formats_display_string) ?></h4>			
		</div>
	</div>
	<div class="row">
		<div class="col offset-md-1">
			<h5 style="display:inline"><strong>Director:</strong></h5><?= ($movie['first_name']) ?> <?= ($movie['last_name'])."
" ?>
			<h5 style="margin-bottom:0"><strong>Cast:</strong></h5>
			<?php foreach (($actors?:[]) as $actor): ?>
			    <?= (trim($actor)) ?> |
			<?php endforeach; ?>
			<h5 style="margin-bottom:0"><strong>Synopsis: </strong></h5>
				<?= (trim($movie['description']))."
" ?>
		</div>
	</div>
	<div class="row mb-5">
		<div class="col offset-md-1">
			<p class="display-4">Reviews</p>
			<?php foreach (($reviews?:[]) as $review): ?>
			    <h5><strong><?= (trim($review['username'])) ?></strong></h5>
			     "<?= (trim($review['review'])) ?>"
			<?php endforeach; ?>
		</div>
	</div>

	<div class="row mb-5">
		<div class="col offset-md-1">
			<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addToCart">
			  Add to Cart
			</button>

			<!-------------------- ADD TO CART ---------------------------->
			<div class="modal fade" id="addToCart" tabindex="-1" role="dialog" aria-labelledby="addToCart" aria-hidden="true">
			  <div class="modal-dialog" role="document">
			    <div class="modal-content">
			      <div class="modal-header">
			        <h5 class="modal-title" id="exampleModalLabel">Add <?= ($movie['title']) ?> to Cart</h5>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          <span aria-hidden="true">&times;</span>
			        </button>
			      </div>
		      	<div class="modal-body">
		      		<form method="post" action="/movies/cart/add/<?= ($movie['movie_id']) ?>">
		      		<div class="row">
				         <div class="form-check-inline">
						  <label class="form-check-label">
						    <input type="radio" class="form-check-input" value="rental" name="buytype"checked>Rent
						  </label>
						</div>
						<div class="form-check-inline">
						  <label class="form-check-label">
						    <input type="radio" class="form-check-input" value="purchase" name="buytype">Purchase
						  </label>
						</div>
		      		</div>
		      		<div class="row">
				         <div class="form-check-inline">
						  <label class="form-check-label">
						    <input type="radio" class="form-check-input" value="vhs" name="format" checked>VHS
						  </label>
						</div>
						<div class="form-check-inline">
						  <label class="form-check-label">
						    <input type="radio" class="form-check-input" value="dvd" name="format">DVD
						  </label>
						</div>
						<div class="form-check-inline">
						  <label class="form-check-label">
						    <input type="radio" class="form-check-input" value="bluray" name="format">Blu-Ray
						  </label>
						</div>
						<div class="form-check-inline">
						  <label class="form-check-label">
						    <input type="radio" class="form-check-input" value="digital"name="format">Digital
						  </label>
						</div>
		      		</div>
				</div>
				<p><?= ($cart_info) ?></p>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			        	<button type="submit" class="btn btn-primary">Add Item to Cart</button>
			        </form>
			      </div>
			    </div>
			  </div>
			</div>

			<!-------------------- REVIEW ---------------------------->
			<button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#leaveAReview">
			  Leave a Review
			</button>

			<!-- Modal -->
			<div class="modal fade" id="leaveAReview" tabindex="-1" role="dialog" aria-labelledby="leaveAReview" aria-hidden="true">
			  <div class="modal-dialog" role="document">
			    <div class="modal-content">
			      <div class="modal-header">
			        <h5 class="modal-title" id="exampleModalLabel">Leave a review for <?= ($movie_title) ?></h5>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          <span aria-hidden="true">&times;</span>
			        </button>
			      </div>
			      <div class="modal-body">
			        <form>
			        	<textarea rows="5" class="form-control" style="height:100%;"></textarea>
			        	<!-- TODO: Add star rating -->
			        	<select class="form-control">
			        		<option>1</option>
			        		<option>2</option>
			        		<option>3</option>
			        		<option>4</option>
			        		<option>5</option>
			        		<option>6</option>
			        		<option>7</option>
			        		<option>8</option>
			        		<option>9</option>
			        		<option>10</option>
			        	</select>
			        </form>
			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			        <button type="button" class="btn btn-primary">Post</button>
			      </div>
			    </div>
			  </div>
			</div>
		</div>
	</div>
</div>
