<?php
namespace App\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Pagination
{
	private $router;
	
    public function __construct(UrlGeneratorInterface $router)
	{
        $this->router = $router;
    }

	public function setPagination($url, $page, $countEntities, $nbMessageByPage, $adjacents = 3)
	{
		$totalPages = ceil($countEntities / $nbMessageByPage);
		$links = [];
		
		$otherParams = null;
		
		if(!isset($url['params']))
			$url['params'] = null;
		else
		{
			if(!isset($url['params']['other_params']))
				$otherParams = null;
			else
				$otherParams = "?".http_build_query($url['params']['other_params']);
		}

		// previous
		if($page==1) {
			$links[] = ["url" => null, "text" => "<", "class" => "browse"];
		}
		else {
			$links[] = ["url" => $this->router->generate($url['url'], array_merge(["page" => $page-1], (array)$url['params'])).$otherParams, "text" => "<", "class" => "browse"];
		}
		
		// first
		if($page>($adjacents+1)) {
			$links[] = ["url" => $this->router->generate($url['url'], array_merge(["page" => 1], (array)$url['params'])).$otherParams, "text" => 1];
		}
		
		// interval
		if($page>($adjacents+2)) {
			$links[] = ["url" => null, "text" => "..."];
		}

		// pages
		$pmin = ($page>$adjacents) ? ($page-$adjacents) : 1;
		$pmax = ($page<($totalPages-$adjacents)) ? ($page+$adjacents) : $totalPages;
		for($i=$pmin; $i<=$pmax; $i++) {
			if($i==$page) {
				$links[] = ["url" => null, "text" => $i, "class" => "active"];
			}
			else {
				$links[] = ["url" => $this->router->generate($url['url'], array_merge(["page" => $i], (array)$url['params'])).$otherParams, "text" => $i];
			}
		}
		
		// interval
		if($page<($totalPages-$adjacents-1)) {
			$links[] = ["url" => null, "text" => "..."];
		}
		
		// last
		if($page<($totalPages-$adjacents)) {
			$links[] = ["url" => $this->router->generate($url['url'], array_merge(["page" => $totalPages], (array)$url['params'])).$otherParams, "text" => $totalPages];
		}
		
		// next
		if($page<$totalPages) {
			$links[] = ["url" => $this->router->generate($url['url'], array_merge(["page" => $page+1], (array)$url['params'])).$otherParams, "text" => ">", "class" => "browse"];
		}
		else {
			$links[] = ["url" => null, "text" => ">", "class" => "browse"];
		}

		return $links;
	}
}