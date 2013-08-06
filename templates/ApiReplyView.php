<?php
  $params = array();
  if (array_key_exists('usermsg', $this->data) == TRUE) $params['usermsg'] = $this->data['usermsg'];
  if (array_key_exists('debugmsg', $this->data) == TRUE) $params['debugmsg'] = $this->data['debugmsg'];
  if (array_key_exists('value', $this->data) == TRUE) $params['value'] = json_decode($this->data['value']);

  echo json_encode($params);
?>
