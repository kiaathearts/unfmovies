<div class="container">
	<div class="row mb-3">
		<div class="col">
			<p class="h1">Customer Search</p>
		</div>
	</div>
	<div class="row mb-3">
		<div class="col">
			<form method="post" action="/admin/customer" class="form-inline">
			    <input class="form-control mr-sm-2" type="search" placeholder="Customer Email" name='email' aria-label="Search">
			    <button class="btn form-control btn-info my-2 my-sm-0" type="submit">Search</button>
			</form>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<h4><strong>Rentals Outstanding</strong></h4>
		</div>
		<div class="col">
			<h4><strong>Rental Cost</strong></h4>
		</div>
		<div class="col">
			<h4><strong>Late Fees</strong></h4>
		</div>
	</div>
	<?php foreach (($outstandings?:[]) as $outstanding): ?>
		<div class="row">
			<div class="col">
				<h4><?= ($outstanding['title']) ?></h4>
			</div>
			<div class="col">
				<h4>$<?= ($outstanding['rental']) ?></h4>
			</div>
			<div class="col">
				<h4>$<?= ($outstanding['fees']) ?></h4>
			</div>
		</div>
	<?php endforeach; ?>
	<div class="row mb-3">
		<div class="col">
			<h4><strong>Balance: $<?= ($balance) ?></strong></h4>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<form method="get" action="/admin/resolve/customer/<?= ($customerid) ?>">
				<button class="btn btn-info my-2 my-sm-0" type="submit">Resolve Balance</button>
			</form>
		</div>
	</div>	
</div>
