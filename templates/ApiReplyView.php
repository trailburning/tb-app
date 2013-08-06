<?php
  $params = array();
  echo "TEST".$this->data['usermsn'];
  if (array_key_exists('usermsg', $this->data)) $params['usermsg'] = $this->data['usermsg'];
  if (array_key_exists('debugmsg', $this->data)) $params['debugmsg'] = $this->data['debugmsg'];
  if (array_key_exists('value', $this->data)) $params['value'] = json_decode($this->data['value']);
  $params['test'] = "COIN";

  echo json_encode($params);
?>
