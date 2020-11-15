
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
	<p class="display-4 mt-3" style="margin-bottom: 10px;">Your Movie Reviews</p>
	<div class="h5" style="margin-bottom: 40px; margin-left: 20px">Because you liked <?= ($preferred_genre) ?> may we suggest <a href="/movies/<?= ($movieid) ?>"><?= ($suggested_movie) ?></a></div>	
	<div class="row" style="margin-bottom: 30px; padding: 0 30px 0 30px;">
		<?php foreach (($reviews?:[]) as $review): ?>
			<div class="col-3 mb-3">
				<h5 class="sm-title"><strong><?= (trim($review['moviename'])) ?></strong></h5>
				(<?= ($review['rating']) ?>/5)<br/>
	     		<em>"<?= (trim($review['review'])) ?>"</em> <br/>
				<!-- - <?= (trim($review['email'])) ?> -->
			</div>
		<?php endforeach; ?>
	</div>
</div>
