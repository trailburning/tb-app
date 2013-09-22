<?php
  $params = array();
  
  // This array_key_exists doesn't work on heroku. It doesn't make sense :(
  // if (array_key_exists('usermsg', $this->data)) $params['usermsg'] = $this->data['usermsg'];
  if (isset($this->data['usermsg'])) $params['usermsg'] = $this->data['usermsg'];
  if (isset($this->data['debugmsg'])) $params['debugmsg'] = $this->data['debugmsg'];
  if (isset($this->data['value'])) $params['value'] = json_decode($this->data['value']);

  echo json_encode($params);
?>
