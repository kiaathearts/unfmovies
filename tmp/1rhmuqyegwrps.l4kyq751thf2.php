<div class="container" style="margin-bottom: 50px;">
	<div class="row mt-3">
		<div class="col col-lg-12 align-self-center">
			<p><?= ($notification) ?></p>
			<?php if ($admin): ?>
				
					<div class="row">
						<p class="display-4">Admin Title Search</p>
					</div>
				
			<?php endif; ?>

			<form method="post" action="/movies/query" class="inline">
				<div class="input-group mb-3">
					<input type="text" name="movie_title" class="form-control" placeholder="Movie Title"/>
					<input type="text" name="movie_director" class="form-control" placeholder="Director"/>
					<input type="text" name="movie_actor" class="form-control" placeholder="Actor"/>
					<select name="movie_genre" class="form-control custom-select">
						<option value="genre" selected>Genre</option>
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
		<?php if ($found==false): ?>
			<div class="col col-lg-12 col-md-12 col-12">
				<p class="h3">Sorry, no movies match your search</p>
			</div>
		<?php endif; ?>
		<div class="row">
		<?php foreach (($grouped_movies?:[]) as $genre=>$movies): ?>
			<?php if (count($movies)>0): ?>
			<div class="col col-12"><p class="h3 group-title"><strong><?= ($genre) ?></strong></p></div>
			<?php foreach (($movies?:[]) as $movie): ?>
				<div class="col col-lg-3 col-md-3 col-3">
					<?php if ($admin): ?>
						
							<a href="/admin/title/<?= ($movie['movie_id']) ?>"><?= ($movie['title']) ?>(<?= ($movie['date_released']) ?>)

								<?php if ($movie['new_release']): ?>
									
										 - <span class="sm-title">New!</span>
									
								<?php endif; ?>
							</a>
						
					<?php endif; ?>
					<?php if ($customer): ?>
						
							<a href="/movies/<?= ($movie['movie_id']) ?>"><?= ($movie['title']) ?>(<?= ($movie['date_released']) ?>)
								<?php if ($movie['new_release'] ==1): ?>
									
										 - <span class="sm-title">New!</span>
									
								<?php endif; ?>
							</a>
						
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
			<?php endif; ?>
		<?php endforeach; ?>
		</div>
<!-- 		<?php foreach (($movies?:[]) as $movie): ?>
			<div class="col col-lg-4 col-md-4 col-4">
				<?php if ($admin): ?>
					
						<a href="/admin/title/<?= ($movie['movie_id']) ?>"><?= ($movie['title']) ?>(<?= ($movie['date_released']) ?>)

							<?php if ($movie['new_release']): ?>
								
									 - <span class="sm-title">New!</span>
								
							<?php endif; ?>
						</a>
					
				<?php endif; ?>
				<?php if ($customer): ?>
					
						<a href="/movies/<?= ($movie['movie_id']) ?>"><?= ($movie['title']) ?>(<?= ($movie['date_released']) ?>)
							<?php if ($movie['new_release'] ==1): ?>
								
									 - <span class="sm-title">New!</span>
								
							<?php endif; ?>
						</a>
					
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
 -->	</div>

</div>