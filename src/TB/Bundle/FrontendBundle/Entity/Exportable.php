<?php 

namespace TB\Bundle\FrontendBundle\Entity;

/**
* 
*/
interface Exportable
{
    /**
     * Returns an array representation the entitiy
     */
    public function export();
}
