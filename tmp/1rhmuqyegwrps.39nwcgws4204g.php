
<div class=container>
	<div class="row">
		<div class="col">
			<p class="h1 mb-3">Search Movie Titles</p>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<form class="form-inline">
			    <input class="form-control mr-sm-2" type="search" placeholder="Search Titles" aria-label="Search">
			    <button class="btn form-control btn-info my-2 my-sm-0" type="submit">Search</button>
			</form>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<p class="h3">No data searched</p>
			<p class="h4">VHS (<?= ($vhs['inventory']) ?>)</p>
			<p class="h4">DVD (<?= ($dvd['inventory']) ?>)</p>
			<p class="h4">Blu-Ray (<?= ($bluray['inventory']) ?>)</p>
		</div>
	</div>
	<div class="row">
		<div class="col"><a class="btn btn-info" href="/admin/<?= ($movieid) ?>/edit">Change Inventory and Pricing</a></div>
	</div>
</div>