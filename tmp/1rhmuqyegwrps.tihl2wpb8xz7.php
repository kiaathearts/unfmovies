<div class="container">
	<div class="row">
		<div class="col">
			<p class="h1 mb-3">Revenue by Title</p>
		</div>
	</div>
	<div class="row mb-3">
		<div class="col">
			<form method="post" action="/admin/reports/title/query" class="inline">
				<div class="input-group mb-3">
					<input type="text" name="movie_title" class="form-control" placeholder="Movie Title"/>
					<input type="text" name="movie_director" class="form-control" placeholder="Director"/>
					<input type="text" name="movie_actor" class="form-control" placeholder="Actor"/>
					<select name="movie_genre" class="form-control custom-select">
						<option selected>Genre</option>
						<?php foreach (($genres?:[]) as $genre): ?>
					  		<option value="<?= ($genre['genre_id']) ?>"><?= ($genre['genre_name']) ?></option>
						<?php endforeach; ?>
					</select>
					<div class="input-group-append">
						<button type="submit" class="btn btn-primary">Search</button>
					</div>
				</div>
			</form>
			<?php if ($found==false): ?>
				<p>No movies matching your criteria were found</p>
			<?php endif; ?>
			<?php foreach (($movies?:[]) as $movie): ?>
				<div class="col col-lg-4 col-md-4 col-4">
					<a href="/admin/reports/title/<?= ($movie['movie_id']) ?>"><?= ($movie['title']) ?>(<?= ($movie['date_released']) ?>)</a>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<?php if ($movie_title): ?>
				<p class="h2 mb-3"><strong>Total Revenue for <?= ($movie_title) ?></strong></p>
			<?php endif; ?>
		</div>
	</div>
	<?php if ($movie_title): ?>
		<div class="row">
			<div class="col">
				<p class="h4"><strong>Total Sales</strong></p>
				<p class="h4">Rentals to Date: $<?= ($total_rentals) ?>.00</p>
				<p class="h4">Purchased to Date: $<?= ($total_purchases) ?>.00</p>
				<p class="h4">Total: $<?= ($totals_rental_purchase) ?>.00</p>
			</div>
		</div>
	<?php endif; ?>
</div>