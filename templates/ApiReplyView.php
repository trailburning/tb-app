<?php

  $params = array();
  if (array_key_exists('usermsg', $this->data)) $params['usermsg'] = $this->data['usermsg'];
  if (array_key_exists('debugmsg', $this->data)) $params['debugmsg'] = $this->data['debugmsg'];
  if (array_key_exists('value', $this->data)) $params['value'] = json_decode($this->data['value']);

  echo json_encode($params);
?>
