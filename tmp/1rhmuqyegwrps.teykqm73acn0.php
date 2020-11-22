<div class="container" style="margin-bottom: 50px">
	<div class="row mb-3">
		<div class="col">
			<p class="h1">Total Revenue for <?= ($genre) ?> by <?= ($interval) ?></p>
		</div>
	</div>
	<!-- TODO: Perform for number of intervals -->
	<div class="row">
		<div class="col">
			<?php foreach (($data?:[]) as $data_key=>$datum): ?>
			    <div>
			        <p></p>
					<h3><strong>Period of:</strong> <?= ($data_key) ?> </h3>
					<h4>Rentals: $<?= ($datum['rentals']['rental_sum']) ?>.00</h4>
					<h4>Purchases: $<?= ($datum['purchases']['purchase_sum']) ?>.00</h4>
					<h4><strong>Total: $<?= ($datum['total']) ?>.00</strong></h4>
			    </div>
			<?php endforeach; ?>
		</div>
	</div>
</container>