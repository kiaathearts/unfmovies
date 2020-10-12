<?php
	class Movie{
		public $title;
		public $actors;
		public $director;
		public $release_year;
		public $synopsis;

		function __construct($title, $actors, $director, $release_year, $synopsis){
			$this->title = $title;
			$this->actors = $actors;
			$this->director = $director;
			$this->release_year = $release_year;
			$this->synopsis = $synopsis;
		}		
	}
?>