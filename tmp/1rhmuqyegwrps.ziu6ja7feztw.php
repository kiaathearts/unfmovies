
<div class="container">
	<div class="row">
		<div class="col">
			<p class="display-4 page-title">Welcome to your profile, <?= ($username) ?> </p>
		</div>
	</div>
	<div class="row" style="margin-top: 20px;">
		<div class="col">
			<h3 style="display:inline">Total Fees (Balance)  $<?= ($balance) ?> </h3>
			<a href="/profile/resolve/<?= ($SESSION['userid']) ?>" class="btn btn-info btn-sm mb-2">Pay Balance</a> 
		</div>
	</div>
	<div class="row">
		<div class="col" style="margin-top: 20px; margin-bottom: 20px;">
			<p class="h3">Current Rental(s)
				<a href="/profile/return/<?= ($SESSION['userid']) ?>" class="btn btn-info btn-sm mb-2">Return Rentals</a>
			</p>
			<?php foreach (($checked_out?:[]) as $rental): ?>
				<p>
					<?= ($rental['title']) ?> - Due: <?= ($rental['due']) ?> 
					<?php if ($rental['late']): ?>
						
							<span class="text-danger">Late!</span>
						
					<?php endif; ?>
				</p>
			<?php endforeach; ?> 
		</div>
	</div>
	<p class="h3"><strong>Your Movie Reviews</strong></p>
	<span class="h5">Because you liked <?= ($preferred_genre) ?> may we suggest <a href="/movies/<?= ($movieid) ?>"><?= ($suggested_movie) ?></a></span>
	<div class="row mt-3">
			<?php foreach (($reviews?:[]) as $review): ?>
				<div class="col-4" style="margin-bottom: 20px;">
			    	<h5 class="sm-title"><strong><?= (trim($review['moviename'])) ?></strong></h5>
			     		"<?= (trim($review['review'])) ?>"
					<div class="sm-title">
						Score: <?= ($review['rating']) ?>/5
					</div>
				</div>
			<?php endforeach; ?>
	</div>
</div>
