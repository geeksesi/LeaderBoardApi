<?php
/**
 * @author Mohammad Javad Ghasemy
 */
class Controller
{

	public $url;
	private $config;
	private $model;
	private $view;

	function __construct()
	{
		$model = new Model();
		$view = new View();
		$this->model = $model;
		$this->view = $view; 
		$this->url();
		$this->controller();
	}

	/**
	 * @return [type]
	 */
	public function controller()
	{

		if (!isset($this->url["cp"]))
		{
			$this->view->page_home();
			return true;
		} 
		if ($this->url["cp"] == "get" && isset($this->url["query"])) 
		{
			$this->view->main_page($this->get());
		}
		elseif ($this->url["cp"] == "set")
		{
			$this->view->main_page($this->set());
		} 
		elseif ($this->url["cp"] == "install") 
		{
			/**
			 * Install MySql Databases and Table 
			 */
			$installation = $this->model->installation();
			if (isset($installation["token"]) && $installation["token"] == true)
			{
				echo "i have good news... your database is ready to use for this token : {$installation['o_token']}";
			}
			elseif ($installation === null) 
			{
				echo "your database is ready...use it." ;
			}
			else
			{
				echo "some bad news..!!!!...WRONG.....!!!! <br>" ;
				var_dump($installation);
			}
		}
		else
		{
			$this->view->error_404();
		}


	}

	private function set()
	{
		$outpot = [];

		if (empty($_POST["token"]) || empty($_POST["score"]) ||  empty($_POST["name"]) || empty($_POST["unix_time"])) 
		{
			$outpot["status"] = "false";
			$outpot["data"]   = "please check your query and fill all required keys";
			return json_encode($outpot);
		}
		if (empty($_POST["hash"])) 
		{
			$outpot["status"] = "false";
			$outpot["data"]   = "please check your query and fill all required keys";
			return json_encode($outpot);
		}
		$hash_data 		= $_POST["hash"];
		$hash_data 		= str_replace(" ", "+", $hash_data);
		$un_hash_json 	= openssl_decrypt($hash_data , HASH_METHOD, HASH_KEY, $options=0, HASH_IV);
		$un_hash_array 	= json_decode($un_hash_json,true);

		if ($_POST["token"] != $un_hash_array["token"] || $_POST["score"] != $un_hash_array["score"] || $_POST["name"] != $un_hash_array["name"]) 
		{
			$outpot["status"] = "false";
			$outpot["data"]   = "Wrong data";
			return json_encode($outpot);
		}

		$token 		= $_POST["token"];
		$score 		= $_POST["score"];
		$name 		= $_POST["name"];
		$date 		=  date('Y-m-d-H:i', time());
		$date_milady=  $date;

		error_log($date_milady);
		list($m_year, $m_month, $m_day, $m_time) = explode("-", $date_milady);
		$date_shamsy = $this->g_to_j($m_year, $m_month, $m_day, "-");
		$date_shamsy = $date_shamsy."-".$m_time;

		$$date_shamsy 	= date('Y-m-d H:i', strtotime($date_shamsy));
		$date_milady  	= date('Y-m-d H:i', strtotime($date_milady));
		$set_score 		= $this->model->set_score($token, $date_milady, $date_shamsy, $_POST["unix_time"], $score, $name);
		if ($set_score !== true) 
		{
			$outpot["status"] = "false";
			$outpot["data"]   = "somthing is wrong";
			return json_encode($outpot);
		}
		
		$outpot["status"] = "ok";
		$outpot["data"]   = "successfuly add data";
		return json_encode($outpot);

	}


	private function get()
	{
		$outpot = [];

		if (empty($_GET["token"]) || empty($_GET["interval"])) 
		{
			$outpot["status"] = "false";
			$outpot["data"]   = "token or interval is empty";
			return json_encode($outpot);
		}
		$repeat 	= (isset($_GET["repeat"]) && $_GET["repeat"] == "off") ? false : true;
		$token 	 	= $_GET["token"];
		$interval 	= $_GET["interval"];
		$order 		= (!empty($_GET["order"])) ? $_GET["order"] : null;

		if ($interval == "all")
		{
			if (isset($_GET["name"]) || $_GET["name"] != "") 
			{
				$name = $_GET["name"];
				$get_model = $this->model->get_all_score($token, $order, $name);

			}
			else
			{
				$get_model = $this->model->get_all_score($token, $order);
			}
		}
		elseif($interval == "custom")
		{
			if (empty($_GET["date"]) || empty($_GET["date_type"])) 
			{
				$outpot["status"] = "false";
				$outpot["data"]   = "date or date_type is empty";
				return json_encode($outpot);
			}
			$date 		= $_GET["date"]; 			
			$date_type 	= $_GET["date_type"]; 			
			list($d_from, $d_to) = explode("|", $date);
			if (empty($d_from)  || empty($d_to)) 
			{
				$outpot["status"] = "false";
				$outpot["data"]   = "date is not valid";
				return json_encode($outpot);				
			}

			if (isset($_GET["name"]) || $_GET["name"] != "") 
			{
				$name = $_GET["name"];
				$get_model = $this->model->get_custom_score($token, $order, $d_from, $d_to, $date_type, $name);

			}
			else
			{
				$get_model = $this->model->get_custom_score($token, $order, $d_from, $d_to, $date_type);
			}
		}
		else
		{
			$message 			= "sorry this value of interval is wrong";
			$outpot["status"] 	= "false";
			$outpot["data"]   	= $message;
			return json_encode($outpot);
		}
		if ($get_model === false) 
		{
			$outpot["status"] = "false";
			$outpot["data"]   = "please check your input";
			return json_encode($outpot);
		}
		
		$message 			= ($repeat == true) ? $get_model : $this->repeat_off($get_model);
		$outpot["status"] 	= "ok";
		$outpot["data"]   	= $message;
		return json_encode($outpot);
	}




	public function repeat_off($data)
	{
		$outpot = [];
		$names	= [];
		foreach($data as $key => $value)
		{
			$is_repeat = false;
			foreach ($names as $name_key => $name_value) 
			{
				error_log("hi");
				if ($value["name"] == $name_value) 
				{
					$is_repeat = true;
				}
			}
			if ($is_repeat == false) 
			{
				$outpot[]	= $value;
				$names[] 	=  $value["name"];
			}
		}

		return $outpot;
	}




	/**
	 * @return [type]
	 */
	private function url()
	{
		if (isset($_SERVER['HTTPS']))
        {
            $this->url["protocol"] = "https";
        }
        else
        {
            $this->url["protocol"] = "http";
        }
        if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI']))
        {
            $this->url["site"] = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }
        elseif (isset($_SERVER['HTTP_HOST']))
        {
            $this->url["site"] = $_SERVER['HTTP_HOST'];
        }
        else
        {
            $this->url = null;
            return false;
        }
        $url_exploded = explode("/",$this->url["site"]);
        // $this->url["ex"] = $url_exploded;
        $this->url["base"] = $url_exploded[0];
        if (isset($url_exploded[1]) && !empty($url_exploded[1]))
        {
        	$query_explod = explode("?", $url_exploded[1]);
	        if (isset($query_explod[1]) && !empty($query_explod[1]))
	        {
        		$this->url["cp"] = $query_explod[0];
        		$this->url["query"] = $query_explod[1];
	        }
	        else
	        {
        		$this->url["cp"] = $url_exploded[1];
	        }
        }
        return $this->url;
	}


	/** Gregorian & Jalali (Hijri_Shamsi,Solar) Date Converter Functions
	Author: JDF.SCR.IR =>> Download Full Version : http://jdf.scr.ir/jdf
	License: GNU/LGPL _ Open Source & Free _ Version: 2.72 : [2017=1396]
	--------------------------------------------------------------------
	1461 = 365*4 + 4/4   &  146097 = 365*400 + 400/4 - 400/100 + 400/400
	12053 = 365*33 + 32/4    &    36524 = 365*100 + 100/4 - 100/100   */
	private function g_to_j($gy,$gm,$gd,$mod=''){
		$g_d_m=array(0,31,59,90,120,151,181,212,243,273,304,334);
		if($gy>1600)
		{
			$jy=979;
			$gy-=1600;
		}
		else
		{
			$jy=0;
			$gy-=621;
		}
		$gy2=($gm>2)?($gy+1):$gy;
		$days=(365*$gy) +((int)(($gy2+3)/4)) -((int)(($gy2+99)/100)) +((int)(($gy2+399)/400)) -80 +$gd +$g_d_m[$gm-1];
		$jy+=33*((int)($days/12053)); 
		$days%=12053;
		$jy+=4*((int)($days/1461));
		$days%=1461;
		if($days > 365)
		{
			$jy+=(int)(($days-1)/365);
			$days=($days-1)%365;
		}
		$jm=($days < 186)?1+(int)($days/31):7+(int)(($days-186)/30);
		$jd=1+(($days < 186)?($days%31):(($days-186)%30));
		return($mod=='')?array($jy,$jm,$jd):$jy.$mod.$jm.$mod.$jd;
	}


	private function j_to_g($jy,$jm,$jd,$mod=''){
	 if($jy>979)
	 {
		$gy=1600;
		$jy-=979;
	 }
	 else
	 {
		$gy=621;
	 }
	 $days=(365*$jy) +(((int)($jy/33))*8) +((int)((($jy%33)+3)/4)) +78 +$jd +(($jm<7)?($jm-1)*31:(($jm-7)*30)+186);
	 $gy+=400*((int)($days/146097));
	 $days%=146097;
	 if($days > 36524)
	 {
		$gy+=100*((int)(--$days/36524));
		$days%=36524;
		if($days >= 365)$days++;
	 }
	 $gy+=4*((int)($days/1461));
	 $days%=1461;
	 if($days > 365)
	 {
		$gy+=(int)(($days-1)/365);
		$days=($days-1)%365;
	 }
	 $gd=$days+1;
	 foreach(array(0,31,(($gy%4==0 and $gy%100!=0) or ($gy%400==0))?29:28 ,31,30,31,30,31,31,30,31,30,31) as $gm=>$v)
	 {
		if($gd<=$v)break;
		$gd-=$v;
	 }
	 return($mod=='')?array($gy,$gm,$gd):$gy.$mod.$gm.$mod.$gd; 
	}




}

