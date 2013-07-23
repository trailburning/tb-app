<?php

namespace TB;

require_once 'Media.php';

class Picture extends Media {
  public function __construct($filename, $tmp_path) {
    $this->filename = $filename;
    $this->tmp_path = $tmp_path;
    $this->tags     = array();

    $this->getMetadata();
  }

  private function getMetadata() {
    $exiftags = exif_read_data($this->tmp_path);
    
    if (array_key_exists('FileSize', $exiftags)) $this->tags['filesize'] = $exiftags['FileSize']; 
    if (array_key_exists('DateTime', $exiftags)) $this->tags['datetime'] = $exiftags['DateTime']; 
    if (array_key_exists('DateTimeOriginal', $exiftags)) $this->tags['datetime'] = $exiftags['DateTimeOriginal']; 
    if (array_key_exists('COMPUTED', $exiftags) && array_key_exists('Width', $exiftags['COMPUTED'])) $this->tags['width'] = $exiftags['COMPUTED']['Width']; 
    if (array_key_exists('COMPUTED', $exiftags) && array_key_exists('Height', $exiftags['COMPUTED'])) $this->tags['height'] = $exiftags['COMPUTED']['Height']; 

    print_r($exiftags);
    print_r($this->tags);
  }
}

?>
