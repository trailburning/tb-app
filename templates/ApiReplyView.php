<?php

class ApiReplyView extends \Slim\View
{
    public function render($template)
    {
        return '{"value": "'.$this->data['value'].'"}';
    }
}

?>
