
<div class="container">
	<div class="row">
		<div class="col offset-md-4">
			<p class="display-4"><?= ($username) ?></p>
		</div>
	</div>
	<div class="row">
		<div class="col offset-md-4">
			<h3 style="display:inline">Total $<?= ($total_cost) ?></h3>
			<button class="btn btn-info btn-sm mb-2">Return Rentals</button> 
		</div>
	</div>
		<div class="row mb-3">
		<div class="col offset-md-1">
			<form class="form-inline">
				<input type="text" class="form-control" placeholder="Change Password"/>
				<div class="input-group-append">
					<button type="button" class="btn btn-info">Change</button>
				</div>
			</form>
		</div>
	</div>
	<div class="row mb-3">
		<div class="col offset-md-1">
			<h3>Because you liked <?= ($preferred_genre) ?> may we suggest <a href="/movies/<?= ($movieid) ?>"><?= ($suggested_movie) ?></a></h3>
		</div>
	</div>
	<div class="row">
		<div class="col offset-md-1">
			<p class="h3">My Movie Reviews</p>
			<?php foreach (($reviews?:[]) as $review): ?>
			    <h5><strong><?= (trim($review['moviename'])) ?></strong></h5>
			     "<?= (trim($review['review'])) ?>"-<?= ($review['rating'])."
" ?>
			<?php endforeach; ?>
		</div>
	</div>
</div>