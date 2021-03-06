<div class="container">
	<div class="row mb-3">
		<div class="col">
			<p class="h1 mb-3">Rental Pricing Adjustment</p>
		</div>
	</div>
	<div class="row">
		<form method="post" action="/admin/1/pricing">
		<div class="row">
			<div class="col">
				<p class="h4">New Releases</p>
			</div>
			<div class="col">
				<input name="new_release" type="text" size="5" placeholder="$<?= ($new_release_price) ?>"/>
			</div>
			<div class="col"></div>
		</div>
		<div class="row mb-3">
			<div class="col">
				<p class="h4">Standard</p>
			</div>
			<div class="col">
				<input name="standard" id="standard" type="text" size="5" placeholder="$<?= ($standard_price) ?>"/>
			</div>
			<div class="col"></div>
			<div class="col"></div>
		</div>
		<div class="row">
			<div class="col">
				<p class="text-danger">ATTENTION!: This operation will update all movies in the database</p>
				<button type="submit" class="btn btn-info">Save Changes</button>
			</div>
		</div>
	</form>
</div>