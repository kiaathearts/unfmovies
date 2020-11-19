
<div class="container" style="margin-bottom: 50px">
	<div class="row">
		<div class="col">
			<p class="h1 mb-3">View <?= ($title) ?> Inventory and Pricing</p>
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
	<form method="post" action="">
		<div class="row">
			<div class="col">
				<p class="h4"><strong>VHS</strong></p>
			</div>
			<div class="col">
				<p><?= ($vhs['location']) ?> - <?= ($vhs['inventory']) ?></p>
			</div>
			<div class="col">
				<p><?= ($vhs['rental']) ?></p>
			</div>
			<div class="col">
				<p><?= ($vhs['purchase']) ?></p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p class="h4"><strong>DVD</strong></p>
			</div>
			<div class="col">
				<p><?= ($dvd['location']) ?> - <?= ($dvd['inventory']) ?></p>
			</div>
			<div class="col">
				<p><?= ($dvd['rental']) ?></p>
			</div>
			<div class="col">
				<p><?= ($dvd['purchase']) ?></p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p class="h4"><strong>Blu-Ray</strong></p>
			</div>
			<div class="col">
				<p><?= ($bluray['location']) ?> - <?= ($bluray['inventory']) ?></p>
			</div>
			<div class="col">
				<p><?= ($bluray['rental']) ?></p>
			</div>
			<div class="col">
				<p><?= ($bluray['purchase']) ?></p>
			</div>
		</div>
			<div class="row">
			<div class="col">
				<p class="h4"><strong>Digital</strong></p>
			</div>
			<div class="col">
				<p>n/a</p>
			</div>
			<div class="col">
				<p><?= ($digital['rental']) ?></p>
			</div>
			<div class="col">
				<p><?= ($digital['purchase']) ?></p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p class="h4"><strong>Availability: </strong>
					<?php if ($available): ?>
					    
					    <span>Available</span><?php else: ?><span>Not available</span>
					<?php endif; ?>
				</p>
			</div>
		</div>
		<div class="row">
			<div class="col"><a class="btn btn-info" href="/admin/<?= ($movieid) ?>/edit">Change Inventory and Pricing</a></div>
		</div>
	</form>
</div>
