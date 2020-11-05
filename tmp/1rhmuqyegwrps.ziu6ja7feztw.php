
<div class="container">
	<div class="row">
		<div class="col offset-md-4">
			<p class="display-4"><?= ($username) ?></p>
		</div>
	</div>
	<div class="row">
		<div class="col offset-md-4">
			<h3 style="display:inline">Total $<?= ($balance) ?> </h3>
			<a href="/profile/return/<?= ($SESSION['userid']) ?>" class="btn btn-info btn-sm mb-2">Return Rentals</a> 
		</div>
	</div>
	<div class="row mb-3">
		<div class="col offset-md-1">
			<form method="post" action="/profile/<?= ($SESSION['userid']) ?>/update" class="form-inline">
				<input type="password" name="password" class="form-control" placeholder="Change Password"/>
				<div class="input-group-append">
					<button type="submit" class="btn btn-info">Change</button>
				</div>
			</form>
				<?php if ($SESSION['show_pass_message']): ?>
					
						<div>
							<p style="color:blue">Password change successful</p>
						</div>
					
				<?php endif; ?>
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
