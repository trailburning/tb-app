<?php

namespace TB;

require_once 'Media.php';

class Picture extends Media {

  public function __construct($filename="", $tmp_path="") {
    $this->coords     = array();
    $this->filename = $filename;
    $this->tmp_path = $tmp_path;
    $this->tags     = array();
  }

  public function setTag($key, $value) { $this->tags[$key] = $value; }
  public function getTags() { return $this->tags; }

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
