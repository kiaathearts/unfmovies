<div class="container">
		<div class="row">
		<div class="col">
			<p class="h1 mb-3">Revenue Report by Genre</p>
		</div>
	</div>
	<div class="row mb-3">
		<div class="col">
			<select class="custom-select custom-select-lg mb-3">
				<option selected>Genre</option>
				<?php foreach (($genres?:[]) as $genre): ?>
				  	<option value="<?= ($genre) ?>"><?= ($genre) ?></option>
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
				<input type="radio" class="form-check-input" name="opttimetype">Weekly
				</label>
			</div>
			<div class="form-check-inline">
				<label class="form-check-label">
				<input type="radio" class="form-check-input" name="opttimetype">Monthly
				</label>
			</div>
			<div class="form-check-inline">
				<label class="form-check-label">
				<input type="radio" class="form-check-input" name="opttimetype">Yearly
				</label>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col">
			From: <input type="date"/>		
		</div>
		<div class="col">
			To: <input type="date"/>		
		</div>
		<div class="col"></div>
		<div class="col"></div>
		<div class="col"></div>
		<div class="col"></div>
	</div>
	<div class="row mt-3">
		<div class="col">
			<button type="button" class="btn btn-info">Generate Report</button>
		</div>
		<div class="col"></div>
		<div class="col"></div>
		<div class="col"></div>
		<div class="col"></div>
		
	</div>
</div>