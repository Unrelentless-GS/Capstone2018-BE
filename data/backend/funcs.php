<?php
	/*
	A class that globalises a bunch of functions multiple scripts can use.
	
	Written by Alden Viljoen
	*/
	
	if(!class_exists("CJukeFunctions")) {
		class CJukeFunctions {
			/*
			Trying to keep the solution native for now, but cURL is also
			an option down the road, should the below become too minimal or redundant in features.
			*/
			public function PostRequest($url, $headers, $params, $callback, $state) {
				$data = http_build_query($params);

				$options = array (
						"http" => array (
							"method" 			=> "POST",
							"header"			=> 
												  "Content-type: application/x-www-form-urlencoded\r\n"
												. "Content-Length: " . strlen($data) . "\r\n"
												. $headers,
							"content" 			=> $data
							)
						);

				$ctx  = stream_context_create($options);
				$result = file_get_contents($url, false, $ctx);
				
				if($callback !== NULL)
					$callback($result, $state);
			}
			
			public function GetRequest($url, $headers) {
				$options = array (
						"http" => array (
							"method" 			=> "GET",
							"header"			=> $headers
							)
						);

				$ctx  = stream_context_create($options);
				return file_get_contents($url, false, $ctx);
			}
		}
	}
	
	$JUKE = new CJukeFunctions;
?>