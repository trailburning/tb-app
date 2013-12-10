<?php

namespace TB;

require_once 'Media.php';

class JpegMedia extends Media 
{

	public function __construct() 
	{
		parent::__construct();
		$this->mimetype = 'image/jpeg';
	}

	public function verifyFileType() 
	{
		if (filesize($this->tmp_path) < 11 || exif_imagetype($this->tmp_path) != 2) {
			throw new \TB\ApiException("Uploaded file with jpeg extension is not a valid jpeg file", 400);
		} 
	}

	public function readMetadata() 
	{
		$exiftags = exif_read_data($this->tmp_path);

		if (isset($exiftags['FileSize'])) { 
			$this->tags['filesize'] = $exiftags['FileSize']; 
		}
		
		if (isset($exiftags['DateTime'])) {
			$t = $exiftags['DateTime']; 
		}
		
		if (isset($exiftags['DateTimeOriginal'])) {
			$t = $exiftags['DateTimeOriginal']; 
		}

		$this->setTag('datetime',intval(strtotime($t)));
		
		if ($this->getTag('datetime') == FALSE) {
			throw new \TB\ApiException("Error parsing image Datetime", 400);
		}

		if (isset($exiftags['COMPUTED']) && isset($exiftags['COMPUTED']['Width'])) {
			$this->setTag('width', $exiftags['COMPUTED']['Width']); 
		}
		
		if (isset($exiftags['COMPUTED']) && isset($exiftags['COMPUTED']['Height'])) {
			$this->setTag('height', $exiftags['COMPUTED']['Height']); 
		}
	}
}

