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
			<!-- <?php if ($customer): ?> -->
			<!-- <p class="h4">Popular on UNFMovies</p> -->
			<!---------------------------/Carousel--------------------------------------->
<!-- 			<div class="card shadow p-2 mb-5 w-100 rounded" style="background-color:#181c24">
				<div id="carouselExampleControls" class="carousel slide mb-3" data-ride="carousel" >
					  <div class="carousel-inner" style="padding: 0 50px 0 90px">
					    <div class="carousel-item active">
					      <a href="/movies/1602"><img src="https://image.tmdb.org/t/p/w300/oJZSajKLJkoTOzSZQN2ZwRnPwHZ.jpg" alt="First slide"></a>
					      <a href="/movies/1627"><img src="https://image.tmdb.org/t/p/w300/pm1KzNEeJ88uN9vXKAFWnneeum9.jpg" alt="Second slide"></a>
					      <a href="/movies/1693"><img src="https://image.tmdb.org/t/p/w300/n8tsrPLem70exhXHfSausV7FlPx.jpg" alt="Third slide"></a>
					    </div>
					    <div class="carousel-item">
	 				      <a href="/movies/1661"><img src="https://image.tmdb.org/t/p/w300/yJQLWoizC5zBjVk9dr2Qo1K1TCI.jpg" alt="Second slide"></a>
				    	  <a href="/movies/1181"><img src="https://image.tmdb.org/t/p/w300/mumarnp1ZBHFdmt2q6x9ELuC3x0.jpg" alt="First slide"></a>
					      <a href="/movies/1237"><img src="https://image.tmdb.org/t/p/w300/nQdBE1P0r4ZrgGqy5EX8sL2kXG6.jpg" alt="Second slide"></a>
					    </div>
					    <div class="carousel-item">
					      <a href="/movies/1406"><img src="https://image.tmdb.org/t/p/w300/idbNSe8zsYKQL97dJApfOrDSdya.jpg" alt="Third slide"></a>
					      <a href="/movies/1598"><img src="https://image.tmdb.org/t/p/w300/y4Q6EU7cLndLAFuDZZdNAD09hMU.jpg " alt="Third slide"></a>
	   			    	  <a href="/movies/1659"><img src="https://image.tmdb.org/t/p/w300/gevSrXe80v51mrE9bTk5eO1dLdU.jpg" alt="First slide"></a>
					    </div>
					  </div>
					  <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
					    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
					    <span class="sr-only">Previous</span>
					  </a>
					  <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
					    <span class="carousel-control-next-icon" aria-hidden="true"></span>
					    <span class="sr-only">Next</span>
					  </a> -->
				<!-- </div> --><!---------------------------/Carousel--------------------------------------->
<!-- 			</div> -->
<!-- 			<div class="d-flex">
				<div><a href=""><p class="h3">Browse All Available Selections</p></a></div>
				<div class="ml-auto"><a href=""><p class="h3">Leave a Review</p></a></div>
			</div>
			<?php endif; ?> -->
			<!-- <div class="row"> -->

			<!-- </div> -->
		</div>
	</div>
	<div class="row">
		<?php if ($has_movies): ?>
			<?php else: ?>
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