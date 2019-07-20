<?php

namespace App\Service;
	
class ImageGenerator
{	
	private $stroke = false;
	private $blur = false;
	private $font;
	private $backgroundImage;
	private $fontColor;
	private $fontSize;
	private $strokeColor;
	private $text;
	private $image;
	private $copyright = [];
	
	public function getCopyright()
	{
		return $copyright;
	}
	
	public function setCopyright($copyright)
	{
		if(!array_key_exists("x", $copyright))
			throw new Exception("Parameter 'x' doesn't exist.");
		if(!array_key_exists("y", $copyright))
			throw new Exception("Parameter 'y' doesn't exist.");
		if(!array_key_exists("text", $copyright))
			throw new Exception("Parameter 'text' doesn't exist.");
		
		$this->copyright = $copyright;
	}
	
	public function setFontSize($fontSize)
	{
		$this->fontSize = $fontSize;
	}
	
	public function setFont($font)
	{
		$this->font = $font;
	}
	
	public function setFontColor($fontColor)
	{
		$this->fontColor = $fontColor;
	}
	
	public function setStrokeColor($strokeColor)
	{
		$this->strokeColor = $strokeColor;
	}

	public function setBlur($blur)
	{
		$this->blur = $blur;
	}
	
	public function setStroke($stroke)
	{
		$this->stroke = $stroke;
	}
	
	public function setText($text)
	{
		$this->text = $text;
	}

	public function setImage($image)
	{
		$this->image = $image;
	}

	public function generate($start_x, $start_y, $max_width)
	{
		//split the string 
		//build new string word for word 
		//check everytime you add a word if string still fits 
		//otherwise, remove last word, post current string and start fresh on a new line 
		// die(qvar_dump($this->text, $this->image));
		$words = explode(" ", $this->text); 
		$string = ""; 
		$tmp_string = ""; 

		for($i = 0; $i < count($words); $i++) {
			$tmp_string .= $words[$i]." "; 

			//check size of string 
			$dim = imagettfbbox($this->fontSize, 0, $this->font, $tmp_string); 

			if($dim[4] < ($max_width - $start_x)) { 
				$string = $tmp_string; 
				$curr_width = $dim[4];
			} else { 
				$i--; 
				$tmp_string = ""; 
				$start_xx = $start_x + round(($max_width - $curr_width - $start_x) / 2);        
				
				if($this->blur && $this->stroke)
				{
					$this->imagettftextblur($this->image, $this->fontSize, 0, $start_xx + 2, $start_y + 2, $this->fontColor, $this->font, $string, 10); 
					$this->imagettftextblur($this->image, $this->fontSize, 0, $start_xx, $start_y, $this->fontColor, $this->font, $string);
					$this->imagettfstroketext($this->image, $this->fontSize, 0, $start_xx, $start_y, $this->fontColor, $this->strokeColor, $this->font, $string, 1);
				}
				elseif($this->blur)
				{
					$this->imagettftextblur($this->image, $this->fontSize, 0, $start_xx + 2, $start_y + 2, $this->fontColor, $this->font, $string, 10); 
					$this->imagettftextblur($this->image, $this->fontSize, 0, $start_xx, $start_y, $this->fontColor, $this->font, $string); 
				}
				elseif($this->blur)
				{
					$this->imagettfstroketext($this->image, $this->fontSize, 0, $start_xx, $start_y, $this->fontColor, $this->strokeColor, $this->font, $string, 1);
				}
				else
					imagettftext($this->image, $this->fontSize, 0, $start_xx, $start_y, $this->fontColor, $this->font, $string);
				
				$string = ""; 
				$start_y += abs($dim[5]) * 2; 
				$curr_width = 0;
			} 
		} 

		$start_xx = $start_x + round(($max_width - $dim[4] - $start_x) / 2);
		
		if($this->blur && $this->stroke)
		{
			$this->imagettftextblur($this->image, $this->fontSize, 0, $start_xx + 2, $start_y + 2, $this->fontColor, $this->font, $string, 10); 
			$this->imagettftextblur($this->image, $this->fontSize, 0, $start_xx, $start_y, $this->fontColor, $this->font, $string);
			$this->imagettfstroketext($this->image, $this->fontSize, 0, $start_xx, $start_y, $this->fontColor, $this->strokeColor, $this->font, $string, 1);
		}
		elseif($this->blur)
		{
			$this->imagettftextblur($this->image, $this->fontSize, 0, $start_xx + 2, $start_y + 2, $this->fontColor, $this->font, $string, 10); 
			$this->imagettftextblur($this->image, $this->fontSize, 0, $start_xx, $start_y, $this->fontColor, $this->font, $string); 
		}
		elseif($this->blur)
		{
			$this->imagettfstroketext($this->image, $this->fontSize, 0, $start_xx, $start_y, $this->fontColor, $this->strokeColor, $this->font, $string, 1);
		}
		else
			imagettftext($this->image, $this->fontSize, 0, $start_xx, $start_y, $this->fontColor, $this->font, $string);            
			
		// imagettftext($image, $this->fontSize, 0, $start_x, $start_y, $this->fontColor, $this->font, );            
	
		$this->imagettfstroketext($this->image, 20, 0, $this->copyright["x"], $this->copyright["y"], $this->strokeColor, $this->fontColor, $this->font, $this->copyright["text"], 1);
	}
	
    private function imagettftextblur(&$image, $size, $angle, $x, $y, $color, $fontfile, $text, $blur_intensity = 0, $blur_filter = IMG_FILTER_GAUSSIAN_BLUR)
	{
        // $blur_intensity needs to be an integer greater than zero; if it is not we
        // treat this function call identically to imagettftext
        if (is_int($blur_intensity) && $blur_intensity > 0) {
            // $return_array will be returned once all calculations are complete
            $return_array = [
                imagesx($image), // lower left, x coordinate
                -1,              // lower left, y coordinate
                -1,              // lower right, x coordinate
                -1,              // lower right, y coordinate
                -1,              // upper right, x coordinate
                imagesy($image), // upper right, y coordinate
                imagesx($image), // upper left, x coordinate
                imagesy($image)  // upper left, y coordinate
            ];
            // $temporary_image is a GD image that is the same size as our
            // original GD image
            $temporary_image = imagecreatetruecolor(
                imagesx($image),
                imagesy($image)
            );
            // fill $temporary_image with a black background
            imagefill(
                $temporary_image,
                0,
                0,
                imagecolorallocate($temporary_image, 0x00, 0x00, 0x00)
            );
            // add white text to $temporary_image with the function call's
            // parameters
            imagettftext(
                $temporary_image,
                $size,
                $angle,
                $x,
                $y,
                imagecolorallocate($temporary_image, 0xFF, 0xFF, 0xFF),
                $fontfile,
                $text
            );
            // execute the blur filters
            for ($blur = 1; $blur <= $blur_intensity; $blur++) {
                imagefilter($temporary_image, $blur_filter);
            }
            // set $color_opacity based on $color's transparency
            $color_opacity = imagecolorsforindex($image, $color)['alpha'];
            $color_opacity = (127 - $color_opacity) / 127;
            // loop through each pixel in $temporary_image
            for ($_x = 0; $_x < imagesx($temporary_image); $_x++) {
                for ($_y = 0; $_y < imagesy($temporary_image); $_y++) {
                    // $visibility is the grayscale of the current pixel multiplied
                    // by $color_opacity
                    $visibility = (imagecolorat(
                        $temporary_image,
                        $_x,
                        $_y
                    ) & 0xFF) / 255 * $color_opacity;
                    // if the current pixel would not be invisible then add it to
                    // $image
                    if ($visibility > 0) {
                        // we know we are on an affected pixel so ensure
                        // $return_array is updated accordingly
                        $return_array[0] = min($return_array[0], $_x);
                        $return_array[1] = max($return_array[1], $_y);
                        $return_array[2] = max($return_array[2], $_x);
                        $return_array[3] = max($return_array[3], $_y);
                        $return_array[4] = max($return_array[4], $_x);
                        $return_array[5] = min($return_array[5], $_y);
                        $return_array[6] = min($return_array[6], $_x);
                        $return_array[7] = min($return_array[7], $_y);
                        // set the current pixel in $image
                        imagesetpixel(
                            $image,
                            $_x,
                            $_y,
                            imagecolorallocatealpha(
                                $image,
                                ($color >> 16) & 0xFF,
                                ($color >> 8) & 0xFF,
                                $color & 0xFF,
                                (1 - $visibility) * 127
                            )
                        );
                    }
                }
            }
            // destroy our $temporary_image
            imagedestroy($temporary_image);
            return $return_array;
        } else {
            return imagettftext(
                $image,
                $size,
                $angle,
                $x,
                $y,
                $color,
                $fontfile,
                $text
            );
        }
    }

	private function imagettfstroketext(&$image, $size, $angle, $x, $y, &$textcolor, &$strokecolor, $fontfile, $text, $px) {
		for($c1 = ($x-abs($px)); $c1 <= ($x+abs($px)); $c1++)
			for($c2 = ($y-abs($px)); $c2 <= ($y+abs($px)); $c2++)
				$bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);
	   return imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
	}
}