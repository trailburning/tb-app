<?php
  $params = array();
  if (isset($this->data['usermsg'])) $params['usermsg'] = $this->data['usermsg'];
  if (array_key_exists('debugmsg', $this->data) == TRUE) $params['debugmsg'] = $this->data['debugmsg'];
  if (array_key_exists('value', $this->data) == TRUE) $params['value'] = json_decode($this->data['value']);

  echo json_encode($params);
?>
