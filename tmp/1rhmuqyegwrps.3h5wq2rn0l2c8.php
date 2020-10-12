
<div class=container>
	<div class="row">
		<div class="col">
			<p class="h1 mb-3">Update <?= ($title) ?> Inventory and Pricing</p>
		</div>
	</div>
	<div class="row mt-3">
		<div class="col"></div>
		<div class="col">
			<p class="h4"><strong>Stock</strong></p>
		</div>
		<div class="col">
			<p class="h4"><strong>Rental Price</strong></p>
		</div>
		<div class="col">
			<p class="h4"><strong>Purchase Price</strong></p>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<p class="h4"><strong>VHS</strong></p>
		</div>
		<div class="col">
			<input type="text" size="5" placeholder="<?= ($vhs['inventory']) ?>"></input>
		</div>
		<div class="col">
			<input type="text" size="5" placeholder="<?= ($vhs['rental']) ?>"></input>
		</div>
		<div class="col">
			<input type="text" size="5" placeholder="<?= ($vhs['purchase']) ?>"></input>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<p class="h4"><strong>DVD</strong></p>
		</div>
		<div class="col">
			<input type="text" size="5" placeholder="<?= ($dvd['inventory']) ?>"></input>
		</div>
		<div class="col">
			<input type="text" size="5" placeholder="<?= ($dvd['rental']) ?>"></input>
		</div>
		<div class="col">
			<input type="text" size="5" placeholder="<?= ($dvd['purchase']) ?>"></input>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<p class="h4"><strong>Blu-Ray</strong></p>
		</div>
		<div class="col">
			<input type="text" size="5" placeholder="<?= ($bluray['inventory']) ?>"></input>
		</div>
		<div class="col">
			<input type="text" size="5" placeholder="<?= ($bluray['rental']) ?>"></input>
		</div>
		<div class="col">
			<input type="text" size="5" placeholder="<?= ($bluray['purchase']) ?>"></input>
		</div>
	</div>
		<div class="row">
		<div class="col">
			<p class="h4"><strong>Digital</strong></p>
		</div>
		<div class="col">
			<input disabled type="text" size="5" placeholder="n/a"></input>
		</div>
		<div class="col">
			<input type="text" size="5" placeholder="<?= ($digital['rental']) ?>"></input>
		</div>
		<div class="col">
			<input type="text" size="5" placeholder="<?= ($digital['purchase']) ?>"></input>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<label for="availability">Available</label>
			<input type="checkbox" id="availability" <?php if ($available): ?>checked<?php endif; ?>>
		</div>
	</div>
	<div class="row">
		<button type="button" class="btn btn-danger mr-2">Delete</button>
		<button type="button" class="btn btn-warning mr-2">Close</button>
		<button type="button" class="btn btn-info">Save</button>
	</div>
</div>