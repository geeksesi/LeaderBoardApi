<?php
/**
 * @author Mohammad Javad Ghasemy
 */
class View
{

	public function error_404()
	{
		include VIEW_DIR."404.php";
	}


	public function page_home()
	{
		include VIEW_DIR."home.php";
	}


	public function main_page($_data = null)
	{
		$data = $_data;
		include VIEW_DIR."main.php";
	}	
}
