
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
	<form method="post" action="/admin/<?= ($movieid) ?>/edit">
		<div class="row">
			<div class="col">
				<p class="h4"><strong>VHS</strong></p>
			</div>
			<div class="col">
				<label for="vhs_inventory_id"><?= ($vhs['location']) ?></label>
				<input id="vhs_inventory_id" name="vhs_inventory_count" type="text" size="5" placeholder="<?= ($vhs['inventory']) ?>"></input>
			</div>
			<div class="col">
				<input name="vhs_rental_cost" type="text" size="5" placeholder="<?= ($vhs['rental']) ?>"></input>
			</div>
			<div class="col">
				<input name="vhs_purchase_cost" type="text" size="5" placeholder="<?= ($vhs['purchase']) ?>"></input>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p class="h4"><strong>DVD</strong></p>
			</div>
			<div class="col">
				<label for="dvd_inventory_id"><?= ($dvd['location']) ?></label>
				<input id="dvd_inventory_id" name="dvd_inventory_count" type="text" size="5" placeholder="<?= ($dvd['inventory']) ?>"></input>
			</div>
			<div class="col">
				<input name="dvd_rental_cost" type="text" size="5" placeholder="<?= ($dvd['rental']) ?>"></input>
			</div>
			<div class="col">
				<input name="dvd_purchase_cost" type="text" size="5" placeholder="<?= ($dvd['purchase']) ?>"></input>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p class="h4"><strong>Blu-Ray</strong></p>
			</div>
			<div class="col">
				<label for="bluray_inventory_id"><?= ($bluray['location']) ?></label>
				<input id="bluray_inventory_id" name="bluray_inventory_count" type="text" size="5" placeholder="<?= ($bluray['inventory']) ?>"></input>
			</div>
			<div class="col">
				<input name="bluray_rental_cost" type="text" size="5" placeholder="<?= ($bluray['rental']) ?>"></input>
			</div>
			<div class="col">
				<input name="bluray_purchase_cost" type="text" size="5" placeholder="<?= ($bluray['purchase']) ?>"></input>
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
				<input name="digital_rental_cost" type="text" size="5" placeholder="<?= ($digital['rental']) ?>"></input>
			</div>
			<div class="col">
				<input name="digital_purchase_cost" type="text" size="5" placeholder="<?= ($vhs['purchase']) ?>"></input>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<label for="availability">Available</label>
				<input name="availability" type="checkbox" value="availability" id="availability" <?php if ($available): ?>checked<?php endif; ?>>
			</div>
		</div>
		<div class="row">
			<button type="submit" name="action" value="delete" class="btn btn-danger mr-2">Delete</button>
			<button type="submit" name="action" value="close" class="btn btn-warning mr-2">Close</button>
			<button type="submit" name="action" value="save" class="btn btn-info">Save</button>
		</div>
	</form>
</div>