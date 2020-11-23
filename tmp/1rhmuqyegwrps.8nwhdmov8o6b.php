<div class="container" style="margin-bottom: 200px;">
	<div class="row mb-2">
		<div class="col">
			<p class="display-4"><?= ($movie['title']) ?></p>
		</div>
	</div>

	<!--------------------MOVIE DATA-------------------------------->
	<div class="card shadow p-2 mb-5 bg-white rounded">
		<div class="card-body">
			<div class="row">
				<div class="col-3">
					<img src="<?= ($movie['image_url']) ?>" style="width: 200px"/>
				</div>
				<div class="col">
					<h5 style="display:inline"><strong>Genre: </strong></h5><?= ($movie['genre_name']) ?> <br/>
					<h5 style="display:inline"><strong>Director: </strong></h5><?= ($movie['first_name']) ?> <?= ($movie['last_name']) ?> <br/>
					<h5 style="margin-bottom:0; display: inline;"><strong>Cast: </strong></h5><?= ($cast)."
" ?>
					<?php foreach (($actors?:[]) as $actor): ?>
					    <?= (trim($actor)) ?> |
					<?php endforeach; ?>

					<h5 style="margin-bottom:0"><strong>Synopsis: </strong></h5>
					<p><?= (trim($movie['description'])) ?></p>
					<!-- Button trigger modal -->
					<a class="btn" style="color: #fff; background-color: #013F70" data-toggle="modal" data-target="#exampleModal"><i class="fa fa-play" aria-hidden="true"> Play Trailer</i></a>
				</div>
			</div>
			<div class="row">
				<div class="col">
					<h5>Available for rent or purchase in <?= ($formats_display_string) ?>!</h5>			
				</div>
			</div>
		</div>
		<hr/>
		<!----------------------------------Action Buttons------------------------------------->
		<div class="row">
			<div class="col" style="margin-left: 20px">
				<?php if ($SESSION['balance']>0): ?>
					
						<p>You have a balance of <?= ($SESSION['balance']) ?> and cannot checkout any movies until this has been resolved. Please, navigate to your profile and resolve your balance.</p>
					
					<?php else: ?>
						<?php if ($available): ?>
							
								<button type="button" class="btn btn-lg" style="color: #fff; background-color: #013F70" data-toggle="modal" data-target="#addToCart">
								  Add to Cart
								</button>
							
							<?php else: ?>
								<button type="button" class="btn btn-lg" style="color: #fff; background-color: #013F70" data-toggle="modal" data-target="#addToCart" disabled>
								  Unavailable
								</button>
							
						<?php endif; ?>
					
				<?php endif; ?>

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
	      			<form method="post" action="/movies/cart/add/<?= ($movie['movie_id']) ?>">
			      		<div class="modal-body">
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
					      </div>
			        	</form>
				    </div>
				  </div>
				</div>

				<!-------------------- REVIEW ---------------------------->
				<button type="button" class="btn btn-secondary btn-lg" data-toggle="modal" data-target="#leaveAReview">
				  Leave a Review
				</button>

				<!-- Modal -->
				<div class="modal fade" id="leaveAReview" tabindex="-1" role="dialog" aria-labelledby="leaveAReview" aria-hidden="true">
				  <div class="modal-dialog" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title" id="exampleModalLabel">Leave a review for <strong><?= ($movie['title']) ?></strong></h5>
				        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">
				        <form method="post" action="/movie/<?= ($userid) ?>/review/<?= ($movieid) ?>">
					        	<textarea name="review" rows="5" class="form-control" style="height:100%;"></textarea>
					        	<!-- TODO: Add star rating -->
					        	<select name="rating" class="form-control">
					        		<option>1</option>
					        		<option>2</option>
					        		<option>3</option>
					        		<option>4</option>
					        		<option>5</option>
					        	</select>
					      </div>
					      <div class="modal-footer">
					        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
					        <button type="submit" class="btn btn-primary">Post</button>
					      </div>
				        </form>
				    </div>
				  </div>
				</div>
			</div>
		</div>
		<!----------------------------------/Action Buttons------------------------------------>
	</div>
	<div class="row">
		<div class="col">
			<?php if ($SESSION['balance']>0): ?>
				
					<p>You have a balance of <?= ($SESSION['balance']) ?> and cannot checkout any movies until this has been resolved. Please, navigate to your profile and resolve your balance.</p>
				
				<?php else: ?>
					
				
			<?php endif; ?>
		</div>
		<div class="col">
			
		</div>
	</div>
	<p class="display-4 mt-3">Reviews</p>
	<div class="row" style="margin-bottom: 30px; padding: 0 30px 0 30px;">
		<?php foreach (($reviews?:[]) as $review): ?>
			<div class="col-3 mb-3 p-0">
				(<?= ($review['review_stars']) ?>/5)<br/>
	     		<em>"<?= (trim($review['review_text'])) ?>"</em> <br/>
				- <?= (trim($review['email']))."
" ?>
			</div>
		<?php endforeach; ?>
	</div>


	<!-- Modal -->
	<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog modal-xl" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h5 class="modal-title" id="exampleModalLabel">View <?= ($movie['title']) ?> Trailer</h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">×</span>
	        </button>
	      </div>
	  <div class="modal-body">
	      <iframe width="100%" height="315" src="<?= ($movie['video']) ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
	  </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	      </div>
	    </div>
	  </div>
	</div>

</div>
<script type="text/javascript">
      $('#exampleModal').modal({
          show: false
      }).on('hidden.bs.modal', function(){
          $(this).find('video')[0].pause();
      });

    $(document).ready(function(){
    	alert('success');
    });
</script>
