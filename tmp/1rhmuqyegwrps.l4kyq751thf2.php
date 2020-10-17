<div class="container">
	<div class="row mt-5">
		<div class="col col-lg-12 align-self-center">
			<p><?= ($notification) ?></p>
			<form method="post" action="/movies/query" class="inline">
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
		</div>
	</div>
	<div class="row">
		<?php if (count($movies) == 0): ?>
			<div class="col col-lg-12 col-md-12 col-12">
				<p class="h3">Sorry, no movies match your search</p>
			</div>
		<?php endif; ?>
		<?php foreach (($movies?:[]) as $movie): ?>
			<div class="col col-lg-4 col-md-4 col-4">
				<a href="/movies/<?= ($movie['movie_id']) ?>"><?= ($movie['title']) ?>(<?= ($movie['date_released']) ?>)</a>
			</div>
		<?php endforeach; ?>
	</div>

</div>