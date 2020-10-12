<div class="container">
	<div class="row mt-5">
		<div class="col col-lg-12 align-self-center">
			<form class="inline">
				<div class="input-group mb-3">
					<input type="text" class="form-control" placeholder="Movie Title"/>
					<input type="text" class="form-control" placeholder="Director"/>
					<input type="text" class="form-control" placeholder="Actor"/>
					<select class="form-control custom-select">
						<option selected>Genre</option>
						<?php foreach (($genres?:[]) as $genre): ?>
					  		<option value="<?= ($genre) ?>"><?= ($genre) ?></option>
						<?php endforeach; ?>
					</select>
					<div class="input-group-append">
						<button type="button" class="btn btn-primary">Search</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>