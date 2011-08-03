<?php

/**
 * Class for manipulating images (e.g. resizing images via PHP)
 * @category admin - this is a function that usually isn't needed during front end page loads.
 *
 */

class ROCKETS_ADMIN_Image extends ROCKETS_ConfigurableObject {

   /**
    * Creates Thumbnail from an image. Thumbnails are cropped if necessary to fit to
    * the target dimensions to preserve the images' aspect ratios.
    *
    * Quirks
    *
    * - image is skipped if a file with the same name exists in the target directory.
    * - Metrolist: file with _ is skipped (in Metrolist, _ signifies 2nd...nth file.
    *
    * @param string $ar['pathOriginal'] - path to original images
    * @param string $ar['pathTarget'] - path to target directory where thumbs will be created
    * @param int $ar['width'] - thumb width
    * @param int $ar['height'] - thumb height
    * @param int $ar['quality'] - 0 ~ 100 - image quality
    *
    */
    public function createThumb($ar = null) {

	$pathToImages = $ar['pathOriginal'];
	$pathToThumbs = $ar['pathTarget'];
	$thumbnail_width = $ar['width'];
	$thumbnail_height = $ar['height'];
	if(!isset($ar["quality"])) $ar['quality'] = 100;

	//////////////////////////////////////////////////////////////////

	// parse path for the extension
	$info = pathinfo($pathToImages);
	$fname = $info['filename'];
	// continue only if this is a JPEG image

	if (!$this->checkIfValidImage($pathToThumbs)) {
	    return false;
	}

	if(self::$DEBUG) echo "{$fname} <br />";
	echo "<h3>Creating thumb {$pathToThumbs}</h3>";

	$myImage = imagecreatefromjpeg( "{$pathToImages}" );
	$width_orig = imagesx( $myImage );
	$height_orig = imagesy( $myImage );

	$ratio_orig = $width_orig/$height_orig;

	if ($thumbnail_width/$thumbnail_height > $ratio_orig) {
	    $new_height = $thumbnail_width/$ratio_orig;
	    $new_width = $thumbnail_width;
	} else {
	    $new_width = $thumbnail_height*$ratio_orig;
	    $new_height = $thumbnail_height;
	}

	$x_mid = $new_width/2;  //horizontal middle
	$y_mid = $new_height/2; //vertical middle

	$process = imagecreatetruecolor(round($new_width), round($new_height));

	imagecopyresampled($process, $myImage, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
	$thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
	imagecopyresampled($thumb, $process, 0, 0, ($x_mid-($thumbnail_width/2)), ($y_mid-($thumbnail_height/2)), $thumbnail_width, $thumbnail_height, $thumbnail_width, $thumbnail_height);

	imagedestroy($process);
	imagedestroy($myImage);
	if(self::$EXECUTE) imagejpeg( $thumb, "{$pathToThumbs}", $ar['quality']);
	return true;

    }
}

?>
