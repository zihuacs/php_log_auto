<?php
        Dapper_Log::warning();
 		foreach($arrConfig as $val)
		{
			if($ret && is_resource($ret))
			{
				if($rtime !== null)
				{
					A();
				}

				if($wtime !== null)
				{
					B();
				}

				self::$socket = $ret;
				return true;
			}
			Dapper_Log::warning();
		}
		Dapper_Log::fatal();
		return false;
?>
