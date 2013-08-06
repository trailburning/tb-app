<?php
  $params = array();
  $params['usermsg'] = $this->data['usermsg'];
  if (array_key_exists('debugmsg', $this->data)) $params['debugmsg'] = $this->data['debugmsg'];
  $params['value'] = json_decode($this->data['value']);
  $params['test'] = "COIN";

  echo json_encode($params);
?>
