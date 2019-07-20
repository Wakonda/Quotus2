<?php
	namespace App\Service;

	use MatthiasMullie\Minify;
	
	class MinifyFile {
		private $file;
		private $minifiedFile;
		
		public function __construct($file)
		{
			$this->file = $file;
			$this->minifiedFile = $this->getMinifyFile();
		}
		
		public function getMinifyFile()
		{
			$pathInfos = pathinfo($this->file);
			
			return $pathInfos['dirname'].'/'.$pathInfos['filename'].'.min.'.$pathInfos['extension'];
		}
		
		public function isCreated()
		{
			if(!file_exists($this->minifiedFile))
				return true;

			return (filemtime($this->file) > filemtime($this->minifiedFile));
		}
		
		public function getExtension()
		{
			$pathInfos = pathinfo($this->file);
			return $pathInfos['extension'];
		}
		
		public function save()
		{
			if($this->isCreated())
			{
				if($this->getExtension() == 'css')
					$minifier = new Minify\CSS($this->file);
				else
					$minifier = new Minify\JS($this->file);
	
				file_put_contents($this->minifiedFile, $minifier->minify());
				
				touch($this->minifiedFile, filemtime($this->file));
			}
			
			return $this->minifiedFile;
		}
	}