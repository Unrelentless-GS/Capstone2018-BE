<?php
	/*
	A class that globalises a bunch of functions multiple scripts can use.
	
	Written by Alden Viljoen
	*/
	
	if(!class_exists("CJukeFunctions")) {
		class CJukeFunctions {
			/*
				Parameter Summary:
				url - String, the URL to connect to
				headers - Array, an array containing headers
				params - Array, an array containing k/v's
				callback - Function(string, object) callback function
				state - Object, a state object
			*/
			public function PostRequest($url, $headers, $params, $callback, $state) {
				$data = http_build_query($params);
				
				$conn = curl_init($url);
				curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($conn, CURLOPT_CUSTOMREQUEST, "POST");
				
				curl_setopt($conn, CURLOPT_POSTFIELDS, $data);
				curl_setopt($conn, CURLOPT_HTTPHEADER, $headers);
				
				$response = curl_exec($conn);
				if($callback !== NULL)
					$callback($response, $state);
			}
			
			/*
				Parameter Summary:
				url - String, the URL to connect to
				headers - Array, an array containing headers
			*/
			public function GetRequest($url, $headers) {
				$conn = curl_init($url);
				curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($conn, CURLOPT_CUSTOMREQUEST, "GET");
				
				curl_setopt($conn, CURLOPT_HTTPHEADER, $headers);
				
				return curl_exec($conn);
			}
			
			/*
				Parameter Summary:
				url - String, the URL to connect to
				headers - Array, an array containing headers
				data - A STRING, containing the body data
				callback - Function(string, object) callback function
				state - Object, a state object
			*/
			public function PutRequest($url, $headers, $data, $callback, $state) {
				$conn = curl_init($url);
				curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($conn, CURLOPT_CUSTOMREQUEST, "PUT");
				
				curl_setopt($conn, CURLOPT_POSTFIELDS, $data);
				curl_setopt($conn, CURLOPT_HTTPHEADER, $headers);
				
				$response = curl_exec($conn);
				if($callback !== NULL)
					$callback($response, $state);
			}
		}
	}
	
	$JUKE = new CJukeFunctions;
?>