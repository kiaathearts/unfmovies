<?php
	class Review{
		public $username;
		public $movieid;
		public $review;

		function __construct($username, $movieid, $review){
			$this->username = $username;
			$this->movieid = $movieid;
			$this->review = $review;
		}

		function get_username(){
			return $this->username;
		}

		function get_movieid(){
			return $this->movieid;
		}

		function get_review(){
			return $this->review;
		}
	}
?>