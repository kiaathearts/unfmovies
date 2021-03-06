<div class="container">
	<div class="row">
		<div class="col">
			<p class="h1 mb-3">Revenue Report by Genre</p>
		</div>
	</div>
	<form method="post" action="/admin/reports/genre">
		<div class="row mb-3">
			<div class="col">
				<select name="movie_genre" class="form-control custom-select">
					<?php foreach (($genres?:[]) as $genre): ?>
				  		<option value="<?= ($genre['genre_id']) ?>"><?= ($genre['genre_name']) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col"></div>
			<div class="col"></div>
			<div class="col"></div>
		</div>
		<div class="row" style="margin-top: -15px; margin-bottom:10px">
			<div class="col">
				<div class="form-check-inline">
					<label class="form-check-label">
					<input type="radio" value="weekly" class="form-check-input" name="opttimetype" checked>Weekly
					</label>
				</div>
				<div class="form-check-inline">
					<label class="form-check-label">
					<input type="radio" value="monthly" class="form-check-input" name="opttimetype">Monthly
					</label>
				</div>
				<div class="form-check-inline">
					<label class="form-check-label">
					<input type="radio" value="yearly" class="form-check-input" name="opttimetype">Yearly
					</label>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col">
				From: <input name="from_date" value="2020-01-01" type="date"/>		
			</div>
			<div class="col">
				To: <input name="to_date" value="<?= ($today) ?>" type="date"/>		
			</div>
			<div class="col"></div>
			<div class="col"></div>
			<div class="col"></div>
		</div>
		<div class="row mt-3">
			<div class="col">
				<button type="submit" class="btn btn-info">Generate Report</button>
			</div>
			<div class="col"></div>
			<div class="col"></div>
			<div class="col"></div>
			<div class="col"></div>
		</div>
	</form>
</div>