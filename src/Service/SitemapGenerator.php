<?php
namespace App\Service;

class SitemapGenerator
{
	private $xml;
	private $baseUrl;
	
	public function __construct($baseUrl, $options = array())
	{
		$this->baseUrl = $baseUrl;
		$this->xml = new \DOMDocument("1.0", "UTF-8");
		
		$urlset = $this->xml->createElement("urlset");
		$urlset->setAttribute("xmlns", "http://www.sitemaps.org/schemas/sitemap/0.9");
		
		if(array_key_exists("video", $options) and $options["video"])
			$urlset->setAttribute("xmlns:video", "http://www.google.com/schemas/sitemap-video/1.1");
		
		if(array_key_exists("image", $options) and $options["image"])
			$urlset->setAttribute("xmlns:image", "http://www.google.com/schemas/sitemap-image/1.1");
	
		$this->xml->appendChild($urlset);
	}
	
	public function addItem($path, $priority = 0.5, $options = array())
	{
		$url = $this->xml->createElement("url");
		$loc = $this->xml->createElement("loc", $this->baseUrl.$path);
		$priority = $this->xml->createElement("priority", $priority);
		
		// Add images
		if(array_key_exists("images", $options))
		{
			foreach($options["images"] as $image_opt)
			{
				$image = $this->xml->createElement("image:image");
				$imageLoc = $this->xml->createElement("image:loc", $this->baseUrl.$image_opt['loc']);
				$image->appendChild($imageLoc);
				
				$url->appendChild($image);
			}
		}
		
		$url->appendChild($loc);
		$url->appendChild($priority);
		
		$urlset = $this->xml->getElementsByTagName("urlset")->item(0);

		$urlset->appendChild($url);
	}
	
	public function save()
	{
		return $this->xml->saveXML();
	}
}