<?php
  echo "{\n";
  if (array_key_exists('usermsg', $this->data)) echo '  "usermsg": "'.$this->data['usermsg']."\"\n";
  if (array_key_exists('debugmsg', $this->data)) echo '  "debugmsg": "'.$this->data['debugmsg']."\"\n";
  if (array_key_exists('value', $this->data)) echo '  "value": '.$this->data['value']."\n";
  echo '}';
?>
