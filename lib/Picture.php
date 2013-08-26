<?php

namespace TB;

require_once 'Media.php';

class Picture extends Media {

  public function __construct() {
    parent::__construct();
  }

  public function verifyFileType() {
    if (filesize($this->tmp_path) < 11 || exif_imagetype ($this->tmp_path) != 2) {
      throw new \TB\ApiException("File with jpeg extension is not a valid jpeg file", 400);
    } 
  }

  public function readMetadata() {
    $exiftags = exif_read_data($this->tmp_path);

    if (isset($exiftags['FileSize'])) $this->tags['filesize'] = $exiftags['FileSize']; 
    if (isset($exiftags['DateTime'])) 
      $t = $exiftags['DateTime']; 
    if (isset($exiftags['DateTimeOriginal'])) 
      $t = $exiftags['DateTimeOriginal']; 

    $this->tags['datetime'] = intval(strtotime($t));
    if ($this->tags['datetime'] == FALSE) 
      throw new \Exception("Error parsing image Datetime");

    if (isset($exiftags['COMPUTED']) && isset($exiftags['COMPUTED']['Width'])) $this->tags['width'] = $exiftags['COMPUTED']['Width']; 
    if (isset($exiftags['COMPUTED']) && isset($exiftags['COMPUTED']['Height'])) $this->tags['height'] = $exiftags['COMPUTED']['Height']; 
  }

}

?>
