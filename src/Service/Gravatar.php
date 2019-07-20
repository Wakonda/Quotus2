<?php
namespace App\Service;

class Gravatar
{
	private $size;
	private $url = "http://www.gravatar.com/avatar/";
	
	public function __construct($size = 120)
	{
		$this->size = $size;
	}
	
	public function getURLGravatar()
	{
		return $this->url.md5($this->generateRandomString(rand(7, 12)))."?d=identicon&s=".$this->size;
	}
	
	public function getURLParameters($url)
	{
		$query = parse_url($url, PHP_URL_QUERY);
		parse_str($query, $params);
		
		return $params;
	}
	
	public function setURLParameters($url, $params)
	{
		$urlArray = parse_url($url);
		$url = $urlArray["scheme"]."://".$urlArray["host"].$urlArray["path"];
		
		return $url."?".http_build_query($params);
	}
	
	// Getter and setter
	public function getSize()
	{
		return $this->size;
	}

	public function setSize($size)
	{
		$this->size = $size;
	}
	
	// Private methods
	private function generateRandomString($length = 10)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		
		for ($i = 0; $i < $length; $i++)
		{
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}

		return $randomString;
	}
}