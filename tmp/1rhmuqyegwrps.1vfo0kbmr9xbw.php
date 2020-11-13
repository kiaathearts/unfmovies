<div class="container">
	<div class="row mb-3">
		<div class="col">
			<p class="h1">Customer Search</p>
		</div>
	</div>
	<?php if ($username): ?>
		
			<p class="h1">Results for <?= ($username) ?></p>
			<?php if (count($rentals)>0): ?>
				
					<p class="h4"><strong>Currently checked out</strong></p>
					<?php foreach (($rentals?:[]) as $rental): ?>
						<p class="h5"><?= ($rental['title']) ?> (<?= ($rental['inventory_type']) ?>) - Due: <?= ($rental['due_datetime']) ?></p>
					<?php endforeach; ?>
				
			<?php endif; ?>
			<?php if (count($outstandings) > 0): ?>
				
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
				
				<?php else: ?>
					<h4>Nothing outstanding</h4>
				
			<?php endif; ?>
			<div class="row mb-3">
				<div class="col">
					<h4><strong>Balance: $<?= ($balance) ?></strong></h4>
				</div>
			</div>
			<div>
				<h4><strong>Eligible Purchase Returns</strong></h4>
					<?php foreach (($purchases?:[]) as $purchase): ?>
						<div class="row">
							<div class="col">
								<h4><a href="/admin/return_purchase/<?= ($purchase['bill_id']) ?>"><?= ($purchase['title']) ?> (<?= ($purchase['inventory_type']) ?>)</a></h4>
							</div>
						</div>
					<?php endforeach; ?>
			</div>
			<div class="row">
				<div class="col">
					<a href="/admin/resolve/customer/<?= ($customerid) ?>" class="btn btn-info my-2 my-sm-0" type="submit">Resolve Balance</a>
				</div>
			</div>	
		
		<?php else: ?>
			<div class="row mb-3">
				<div class="col">
					<form method="post" action="/admin/customer" class="form-inline">
					    <input class="form-control mr-sm-2" type="search" placeholder="Customer Email" name='email' aria-label="Search">
					    <button class="btn form-control btn-info my-2 my-sm-0" type="submit">Search</button>
					</form>
				</div>
			</div>
		
	<?php endif; ?>
</div>
