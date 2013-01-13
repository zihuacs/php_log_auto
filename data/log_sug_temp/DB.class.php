<?php
/**
 * DapperPHP (php轻量级框架)
 *
 * 数据库代理操作类
 * @package		DB
 * @author		zhaoshunyao <zhaoshunyao@baidu.com>
 * @since		2011-06-10
 */
class Dapper_Model_DB
{
	private $_mysqli = null;
	private $_dbname = '';
	private $_config = null;
	private $_lastSql = '';
	private $_lastErrno = -1;
	private $_lastErrmsg = '';
	private $_lastIP = '';
	private $_queryNum = 0;		//SQL查询次数
	private $_querySql = array();	//一次连接MYSQL查询的所有语句
	private $_queryTime = array();	//一次连接MYSQL查询的所有语句运行时间
	
	/**
	 * Array of DBProxy instances
	 * @var array
	 */
	private static $_instances = array();

	/**
	 * Get a DBProxy instance for the specified database.
	 *
	 * @param string $database
	 */
	public static function getInstance($database)
	{
		if(empty($database))
		{
			return null;
		}
		
		if(!isset(self::$_instances[$database]))
		{
			$confObj = LibFactory::getInstance('LibConfig');
			$dbConf = $confObj->getConfig('db');
			$config = $dbConf[$database][RUNTIME];
			if(!isset($config['username']))
			{
				return null;
			}
			
			self::$_instances[$database] = null;
			$_config = array('username' => $config['username'],
							'password' => $config['password'],
							'timeout' => $config['timeout'],
							'port' => $config['port'],
							'hosts' => $config['hosts'],
						);
			$dbproxy = new Dapper_Model_DB($database, $_config);
			if($dbproxy->_mysqli && ($dbproxy->_lastErrno == 0))
			{
				self::$_instances[$database] = $dbproxy;
				return self::$_instances[$database];
			}
			else
			{
				//header("location:/index.php/404");
				header("HTTP/1.1 400 Bad Request");
				exit(0);
			}
		}
		else
		{
			return self::$_instances[$database];
		}
	}

	/**
	 * DBProxy Constructor
	 *
	 * @param string $dbname	Database of this dbproxy instance wants to use
	 * @param array $config		Config of the dbproxy instance, as the following format:
	 * <code>
	 * array('username' => '',		// username to access dbproxy server
	 * 		 'password' => '',		// password to access dbproxy server
	 *		 'timeout' => xx,	// retry times when failed to connect dbproxy cluster
	 *		 'port' => xx,			// dbproxy server port
	 *		 'hosts' => array(ip1, ip2, ...),	// dbproxy server ips
	 *		)
	 * </code>
	 */
	public function __construct($dbname, array $config)
	{
		$this->_config = $config;
		$this->_dbname = $dbname;
		$this->_lastSql = '';
		$this->_mysqli = $this->_createConnection();
	}

	/**
	 * DBProxy destructor.
	 * It will close all dbproxy connections created by current instance.
	 */
	public function __destruct()
	{
		if($this->_mysqli)
		{
			mysqli_close($this->_mysqli);
			$this->_mysqli = null;
		}
	}

	/**
	 * Create dbproxy connection according to the config saved
	 * @return dblink resources
	 */
	private function _createConnection()
	{
		$arrHosts = $this->_config['hosts'];
		if(empty($arrHosts))
		{
			Dapper_Log::warning('DB_config_fail:hosts error', 'dal');
			return false;
		}
		
		$intTimeoutSec = intval($this->_config['timeout']);
        $intTimeoutSec = $intTimeoutSec > 2 ? 2 : $intTimeoutSec;
		shuffle($arrHosts);
		//每个host地址最多尝试连接两次
		$arrHosts = array_merge($arrHosts, $arrHosts);
		$intHosts = count($arrHosts);
		for($i = 0; $i < $intHosts; $i++)
		{
			$mysqli = mysqli_init();
			mysqli_options($mysqli, MYSQLI_OPT_CONNECT_TIMEOUT, $intTimeoutSec);
			mysqli_options($mysqli, 11, 3); //READ_TIMEOUT
			mysqli_options($mysqli, 12, 3); //WRITE_TIMEOUT
		
			$host = $arrHosts[$i];
			@mysqli_real_connect($mysqli,$host,$this->_config['username'],$this->_config['password'],$this->_dbname,$this->_config['port']);
			if($errno = mysqli_connect_errno())
			{
				$mysqli = null;
				$this->_lastErrno = $errno;
				$this->_lastErrmsg = mysqli_connect_error();
				$this->_lastIP = $host;
				
				$logArr = array(
					'host' => $host,
					'errno' => $this->_lastErrno,
					'errmsg' => $this->_lastErrmsg
				);
				Dapper_Log::warning('DB_singlepoint_fail:', 'dal', $logArr);
				unset($logArr);
				usleep(500000); //0.5s
				continue;
			}
			else
			{
				$this->_lastErrno = 0;
				$this->_lastErrmsg = '';
				$this->_lastIP = $host;

				mysqli_set_charset($mysqli, 'utf8');
				return $mysqli;
			}
		}

		$logArr = array(
			'host' => $host,
			'errno' => $this->_lastErrno,
			'errmsg' => $this->_lastErrmsg
		);
		Dapper_Log::fatal('DB_connect_fail:', 'dal', $logArr);
		return false;
	}

	/**
	 * Whether the DBlink is workable.
	 * @return bool
	 */
	private function _checkup()
	{
		if(empty($this->_mysqli))
		{
			return false;
		}
		else
		{
			$errno = mysqli_errno($this->_mysqli);
			if($errno == 2003 || $errno == 2006 || $errno == 2013)
			{
				//2003：Can't connect to MySQL server on 'hostname' (4,110) （此情况一般是网络超时、数据库压力过大等导致）
				//2006：MySQL server has gone away （dbproxy在重启时可能会出现此问题，sleep 状态的链接）
				//2013：Lost connection to MySQL server during query （dbproxy在重启时可能会出现此问题，正在执行query）
				
				mysqli_close($this->_mysqli);
				$this->_mysqli = $this->_createConnection();
			}
			return true;
		}
	}
	
	/**
	 * close current connections.
	 */
	public function close()
	{
		if($this->_mysqli)
		{
			mysqli_close($this->_mysqli);
			$this->_mysqli = null;
		}
	}
	
	/**
	 * Perform a query on the database
	 * @param string $strSql	The query string
	 * @return bool Returns true on success or false on failure
	 */
	public function doQuery($strSql)
	{
		if(!$this->_checkup())
		{
			return false;
		}

		$startTime = microtime(true);
		$this->_lastSql = $strSql;
		$ret = mysqli_query($this->_mysqli, $this->_lastSql);
		$endTime = microtime(true);
		
		$this->_queryNum++;
		$this->_querySql[] = $this->_lastSql;
		$this->_queryTime[] = $endTime - $startTime;
		
		if($ret === false)
		{
			$logArr = array(
				'host' => $this->_lastIP,
				'errno' => $this->getErrno(),
				'errmsg' => $this->getErrmsg(),
				'sql' => $strSql
			);
			Dapper_Log::warning('DB_write_fail:', 'dal', $logArr);
		}
		return $ret;
	}
	
	/**
	 * Perform a select query on the database and retriev all the result rows
	 * @param string $strSql	The query string
	 * @return bool|array	Return result rows on success or false on failure
	 */
	public function queryAllRows($strSql)
	{
		if(!$this->_checkup())
		{
			return false;
		}
		
		$startTime = microtime(true);
		$this->_lastSql = $strSql;
		$objRes = mysqli_query($this->_mysqli, $this->_lastSql);
		if(!$objRes)
		{
			$logArr = array(
				'host' => $this->_lastIP,
				'errno' => $this->getErrno(),
				'errmsg' => $this->getErrmsg(),
				'sql' => $strSql
			);
			Dapper_Log::warning('DB_read_fail:', 'dal', $logArr);
			return false;
		}

		$arrResult = array();
		while($arrTmp = mysqli_fetch_assoc($objRes))
		{
			$arrResult[] = $arrTmp;
		}
		$endTime = microtime(true);
		
		$this->_queryNum++;
		$this->_querySql[] = $this->_lastSql;
		$this->_queryTime[] = $endTime - $startTime;

		return $arrResult;
	}

	/**
	 * Perform a select query on the database and retriev the first row in results
	 * @param string $strSql	The query string
	 * @return bool|array	Return result row on success or false on failure
	 */
	public function queryFirstRow($strSql)
	{
		if(!$this->_checkup())
		{
			return false;
		}

		$startTime = microtime(true);
		$this->_lastSql = $strSql;
		$objRes = mysqli_query($this->_mysqli, $this->_lastSql);
		if(!$objRes)
		{
			$logArr = array(
				'host' => $this->_lastIP,
				'errno' => $this->getErrno(),
				'errmsg' => $this->getErrmsg(),
				'sql' => $strSql
			);
			Dapper_Log::warning('DB_read_fail:', 'dal', $logArr);
			return false;
		}

		$arrResult = mysqli_fetch_assoc($objRes);
		$endTime = microtime(true);

		$this->_queryNum++;
		$this->_querySql[] = $this->_lastSql;
		$this->_queryTime[] = $endTime - $startTime;
		
		if($arrResult)
		{
			return $arrResult;
		}
		return false;
	}
	
	/**
	 * Perform a select query on the database and retriev the specified field value in the first row result
	 * @param string $strSql	The query string
	 * @param bool $isInt		Whether the specified field is integer type
	 * @return bool|int|string	Return field value on success or false on failure
	 */
	public function querySpecifiedField($strSql, $isInt = false)
	{
		if(!$this->_checkup())
		{
			return false;
		}

		$startTime = microtime(true);
		$this->_lastSql = $strSql;
		$objRes = mysqli_query($this->_mysqli, $this->_lastSql);
		if (!$objRes)
		{
			$logArr = array(
				'host' => $this->_lastIP,
				'errno' => $this->getErrno(),
				'errmsg' => $this->getErrmsg(),
				'sql' => $strSql
			);
			Dapper_Log::warning('DB_read_fail:', 'dal', $logArr);
			return false;
		}
	
		$out = null;
		$arrResult = mysqli_fetch_row($objRes);
		if($arrResult)
		{
			if($isInt)
			{
				$out = intval($arrResult[0]);
			}
			$out = $arrResult[0];
		}
		else
		{
			if($isInt)
			{
				$out = 0;
			}
			$out = null;
		}
		$endTime = microtime(true);

		$this->_queryNum++;
		$this->_querySql[] = $this->_lastSql;
		$this->_queryTime[] = $endTime - $startTime;
		
		return $out;
	}

	/**
	 * Do multiple sql queries as a transaction
	 *
	 * @param array $arrSql	Array of sql queries to be executed
	 * @return bool Returns true on success or false on failure
	 */
	public function doTransaction(array $arrSql)
	{
		if(!$this->_checkup())
		{
			return false;
		}

		mysqli_autocommit($this->_mysqli, false);
		
		foreach($arrSql as $strSql)
		{
			$ret =  mysqli_query($this->_mysqli, $strSql);
			if(!$ret)
			{
				$this->_lastSql = $strSql;
				mysqli_rollback($this->_mysqli);
				mysqli_autocommit($this->_mysqli, true);
				return false;
			}
		}

		mysqli_commit($this->_mysqli);
		mysqli_autocommit($this->_mysqli, true);
		
		$this->_queryNum++;
		$this->_querySql[] = $this->_lastSql;
		
		return true;
	}

	/**
	 * Selects the defaut database for database queries
	 * @param string $database	The database name
	 * @return bool Returns true on success or false on failure
	 */
	public function selectDB($dbname)
	{
		if(!$this->_checkup())
		{
			return false;
		}
		return mysqli_select_db($this->_mysqli, $dbname);
	}
	
	/**
	 * Get the last inserted data's autoincrement id
	 * @return int
	 */
	public function getLastInsertID()
	{
		if(!$this->_mysqli)
		{
			return false;
		}
		return mysqli_insert_id($this->_mysqli);
	}

	/**
	 * Get number of affected rows of the last SQL query
	 * @return int
	 */
	public function getAffectedRows()
	{
		if(!$this->_mysqli)
		{
			return false;
		}
		return mysqli_affected_rows($this->_mysqli);
	}

	/**
	 * Escapes special characters in a string for use in a SQL query
	 * @param string $str	String to be escaped
	 * @return bool|string	Return escaped string on success or false on failure
	 */
	public function realEscapeString($str)
	{
		if(!$this->_mysqli)
		{
			return false;
		}
		return mysqli_real_escape_string($this->_mysqli, $str);

	}

	/**
	 * Get errno
	 */
	public function getErrno()
	{
		if($this->_mysqli)
		{
			return mysqli_errno($this->_mysqli);
		}
		else
		{
			return -1;
		}
	}

	/**
	 * Get errmsg
	 */
	public function getErrmsg()
	{
		if($this->_mysqli)
		{
			return mysqli_error($this->_mysqli);
		}
		else
		{
			return 'mysql server not available';
		}
	}

	/**
	 * 得到最后查询的SQL
	 */
	public function getSqlStr()
	{
		return $this->_lastSql;
	}
	
	/**
	 * 得到查询次数
	 */
	public function getQueryNum()
	{
		return $this->_queryNum;
	}
	
	/**
	 * 得到全部查询的SQL
	 */
	public function getQuerySql()
	{
		return $this->_querySql;
	}
	
	/**
	 * 得到查询的SQL执行时间
	 */
	public function getQueryTime()
	{
		return $this->_queryTime;
	}
}
?>
