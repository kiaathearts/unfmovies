<div class="container">
	<div class="row mb-3">
		<div class="col">
			<p class="h1">Pricing Adjustment</p>
		</div>
	</div>
	<div class="row">
		<form method="post" action="/admin/1/pricing">
			<div class="col">
				<p class="h3"><strong>Rentals - New Releases</strong></p>
			</div>
			<div class="col">
				<input name="new_release" type="text" size="5" placeholder="$<?= ($new_release_price) ?>"/>
			</div>
			<div class="col"></div>
		</div>
		<div class="row mb-3">
			<div class="col">
				<p class="h3"><strong>Rentals - Standard</strong></p>
			</div>
			<div class="col">
				<input name="standard" type="text" size="5" placeholder="$<?= ($standard_price) ?>"/>
			</div>
			<div class="col"></div>
		</div>
		<div class="row">
			<div class="col">
				<button type="submit" class="btn btn-info">Save Changes</button>
			</div>
		</div>
	</form>
</div>