
<div class=container>
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
				<p><?= ($vhs['inventory']) ?></p>
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
				<p><?= ($dvd['inventory']) ?></p>
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
				<p><?= ($bluray['inventory']) ?></p>
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
				<p><?= ($vhs['purchase']) ?></p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p>Availability</p>
				<?php if ($available): ?>
				    
				    <span>Available</span><?php else: ?><span>Not available</span>
				<?php endif; ?>
			</div>
		</div>
		<div class="row">
			<div class="col"><a class="btn btn-info" href="/admin/<?= ($movieid) ?>/edit">Change Inventory and Pricing</a></div>
		</div>
	</form>
</div>
<!-- <div class=container>
	<div class="row">
		<div class="col">
			<p class="h1 mb-3">Search Movie Titles</p>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<form method="post" action="/admin/title" class="form-inline">
			    <input class="form-control mr-sm-2" type="search" name="title" placeholder="Search Titles" aria-label="Search">
			    <button class="btn form-control btn-info my-2 my-sm-0" type="submit">Search</button>
			</form>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<p class="h3">No data searched</p>
			<?php if ($title_searched): ?>
				<p class="h4">VHS (<?= ($vhs['inventory']) ?>)</p>
				<p class="h4">DVD (<?= ($dvd['inventory']) ?>)</p>
				<p class="h4">Blu-Ray (<?= ($bluray['inventory']) ?>)</p>
			<?php endif; ?>
		</div>
	</div>
	<div class="row">
		<div class="col"><a class="btn btn-info" href="/admin/<?= ($movieid) ?>/edit">Change Inventory and Pricing</a></div>
	</div>
</div> -->