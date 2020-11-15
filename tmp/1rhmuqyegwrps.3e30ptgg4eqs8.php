<div class="container">
	<div class="row mb-3">
		<div class="col">
			<h1>Rental Pricing Adjustment</h1>
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
				<button type="submit" class="btn btn-info">Save Changes</button>
			</div>
		</div>
	</form>
</div>