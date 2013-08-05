<?php

namespace TB;

require_once 'Media.php';

class Picture extends Media {

  public function __construct($filename, $tmp_path) {
    $this->coords     = array();
    $this->filename = $filename;
    $this->tmp_path = $tmp_path;
    $this->tags     = array();

    $this->readMetadata();
  }

  private function readMetadata() {
    $exiftags = exif_read_data($this->tmp_path);

    if (array_key_exists('FileSize', $exiftags)) $this->tags['filesize'] = $exiftags['FileSize']; 
    if (array_key_exists('DateTime', $exiftags)) 
      $t = $exiftags['DateTime']; 
    if (array_key_exists('DateTimeOriginal', $exiftags)) 
      $t = $exiftags['DateTimeOriginal']; 

    $this->tags['datetime'] = intval(strtotime($t));
    if ($this->tags['datetime'] == FALSE) 
      throw new \Exception("Error parsing image Datetime");

    if (array_key_exists('COMPUTED', $exiftags) && array_key_exists('Width', $exiftags['COMPUTED'])) $this->tags['width'] = $exiftags['COMPUTED']['Width']; 
    if (array_key_exists('COMPUTED', $exiftags) && array_key_exists('Height', $exiftags['COMPUTED'])) $this->tags['height'] = $exiftags['COMPUTED']['Height']; 
  }

  public function setCoords($long, $lat) {
    $this->coords[0] = $long;
    $this->coords[1] = $lat;
  }

  public function setId($id) { $this->id = $id ; }
  public function getId() { return $this->id ; }
}

?>
