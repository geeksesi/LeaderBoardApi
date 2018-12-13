<?php
/**
 * @author Mohammad Javad Ghasemy
 */
class Model
{
	private $db;
	private $default_token;
	function __construct()
	{

		/**
		 * generate token by : sha1(mt_rand(1, 90000) . 'Online_Leader_Bord');
		 *
		 * it's use by make an default database...if you want to make another database you can copy mysql query on installation and run it on mysql (actually you should change it :D )
		 *
		 * @var string
		 */
		$this->default_token = DEFAULT_TOKEN;

		// Create connection
		$connect = new mysqli(DB_SERVER_NAME, DB_USER_NAME, DB_PASSWORD, DB_NAME);
		
		if ($connect->connect_error) 
		{
			$this->db = false;
		}
		$this->db = $connect;
	}

	

	/**
	 * Install mysql 
	 * @return [type] [description]
	 */
	public function installation()
	{
		if (is_null($this->db) || $this->db === false || !isset($this->default_token) || empty($this->default_token)) 
		{
			return false;
		}
		if ($check_table = $this->db->query("SHOW TABLES LIKE '".$this->default_token."'")) 
		{
		    if($check_table->num_rows == 1) 
		    {
		        return null;
		    }
		}
		$creat_token_table = "
			CREATE TABLE {$this->default_token} (
				id INT(5) AUTO_INCREMENT ,
				name VARCHAR(40) ,
				date_shamsy DATE ,
				date_milady DATE ,
				date_unix BIGINT ,
				score BIGINT,
				timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ,
				PRIMARY KEY (id)
			) 
			ENGINE = InnoDB CHARSET=utf8 COLLATE utf8_bin;
		";

		if ($this->db->query($creat_token_table) === TRUE) 
		{
			$ret["token"] 	= true; 
			$ret["o_token"] = $this->default_token; 
		} 
		else 
		{
			$ret["token"] = "Error creating table: " . $this->db->error; 
		}
		return $ret;
	}
	

	/**
	 * [set_score description]
	 * @param [type] $_token       [description]
	 * @param [type] $_date_milady [description]
	 * @param [type] $_date_shamsy [description]
	 * @param [type] $_score       [description]
	 * @param [type] $_name        [description]
	 */
	public function set_score($_token, $_date_milady, $_date_shamsy,$_unix_time, $_score, $_name)
	{
		if (is_null($_token) || is_null($_date_milady) || is_null($_score) || is_null($_date_shamsy) || is_null($_name)) 
		{
			return false;
		}
		if (is_null($this->db) || $this->db === false ) 
		{
			return false;
		}
		$set_query = "INSERT INTO {$_token} (date_milady, date_shamsy, date_unix, score, name) VALUES ('{$_date_milady}', '{$_date_shamsy}', '{$_unix_time}', '{$_score}', '{$_name}')";

		if ($this->db->query($set_query ) === TRUE) 
		{
		    return true;
		}
		else 
		{
			error_log($set_query ." :::: ::: ::: ::: ".$this->db->error);
		    return false;
		}
	}


	/**
	 * [get_all_score description]
	 * @param  [type] $_token [description]
	 * @param  [type] $_order [description]
	 * @param  string $_name  [description]
	 * @return [type]         [description]
	 */
	public function get_all_score($_token, $_order, $_name = '')
	{
		if (is_null($_token)) 
		{
			return false;
		}
		if (is_null($this->db) || $this->db === false ) 
		{
			return false;
		}
		if ($_name != '' ) 
		{
			if ($_order === null) 
			{
				$all_score_query = "SELECT *  FROM {$_token} WHERE name='{$_name}'";
			}
			else
			{
				$all_score_query = "SELECT *  FROM {$_token} WHERE name='{$_name}' ORDER BY score {$_order}";
			}
		}
		else
		{
			if ($_order === null) 
			{
				$all_score_query = "SELECT *  FROM {$_token}";
			}
			else
			{
				$all_score_query = "SELECT *  FROM {$_token} ORDER BY score {$_order}";
			}
		}
		$all_score_result = $this->db->query($all_score_query);
		if ($all_score_result->num_rows <= 0) 
		{
		    return null;
		}
		$export = [];
		while($row = $all_score_result->fetch_assoc()) 
		{
        	$export[] = $row;
    	}
		return $export;
	}




	/**
	 * [get_custom_score description]
	 * @param  [type] $_token     [description]
	 * @param  [type] $_order     [description]
	 * @param  [type] $_date_from [description]
	 * @param  [type] $_date_to   [description]
	 * @param  [type] $_date_type [description]
	 * @param  string $_name      [description]
	 * @return [type]             [description]
	 */
	public function get_custom_score($_token, $_order, $_date_from , $_date_to, $_date_type, $_name = '')
	{
		if (is_null($_token) || is_null($_date_from) || is_null($_date_type)) 
		{
			return false;
		}
		if (is_null($this->db) || $this->db === false ) 
		{
			return false;
		}
		if ($_name != '' ) 
		{
			if ($_order === null) 
			{
				$custom_score_query = "SELECT *  FROM {$_token} WHERE name='{$_name}' AND date_{$_date_type} BETWEEN '{$_date_from}' AND '{$_date_to}'";
			}
			else
			{
				$custom_score_query = "SELECT *  FROM {$_token} WHERE name='{$_name}' AND date_{$_date_type} BETWEEN '{$_date_from}' AND '{$_date_to}' ORDER BY score {$_order}";
			}
		}
		else
		{
			if ($_order === null) 
			{
				$custom_score_query = "SELECT *  FROM {$_token} WHERE date_{$_date_type} BETWEEN '{$_date_from}' AND '{$_date_to}'";
			}
			else
			{
				$custom_score_query = "SELECT *  FROM {$_token} WHERE date_{$_date_type} BETWEEN '{$_date_from}' AND '{$_date_to}' ORDER BY score {$_order}";
			}
		}
		$custom_score_result = $this->db->query($custom_score_query);
		if ($custom_score_result->num_rows <= 0) 
		{
			error_log($custom_score_query);
		    return null;
		}
		$export = [];
		while($row = $custom_score_result->fetch_assoc()) 
		{
        	$export[] = $row;
    	}
		return $export;
	}




}
