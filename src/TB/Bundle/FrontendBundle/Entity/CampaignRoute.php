<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CampaignRoute
 *
 * @ORM\Table(name="campaign_route")
 * @ORM\Entity
 */
class CampaignRoute
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="campaign_id", type="integer")
     */
    private $campaignId;

    
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="route_id", type="integer")
     */
    private $routeId;
    
    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="campaignRoutes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="campaign_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $campaign;


    /**
     * @var Route
     *
     * @ORM\ManyToOne(targetEntity="Route", inversedBy="campaignRoutes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="route_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $route;

    /**
     * Set campaign_id
     *
     * @param integer $campaignId
     * @return CampaignRoute
     */
    public function setCampaignId($campaignId)
    {
        $this->campaignId = $campaignId;

        return $this;
    }

    /**
     * Get campaign_id
     *
     * @return integer 
     */
    public function getCampaignId()
    {
        return $this->campaignId;
    }

    /**
     * Set route_id
     *
     * @param integer $routeId
     * @return CampaignRoute
     */
    public function setRouteId($routeId)
    {
        $this->routeId = $routeId;

        return $this;
    }

    /**
     * Get route_id
     *
     * @return integer 
     */
    public function getRouteId()
    {
        return $this->routeId;
    }

    /**
     * Set campaign
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Campaign $campaign
     * @return CampaignRoute
     */
    public function setCampaign(\TB\Bundle\FrontendBundle\Entity\Campaign $campaign = null)
    {
        $this->campaign = $campaign;
        $this->setCampaignId($campaign->getId());

        return $this;
    }

    /**
     * Get campaign
     *
     * @return \TB\Bundle\FrontendBundle\Entity\Campaign 
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * Set route
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $route
     * @return CampaignRoute
     */
    public function setRoute(\TB\Bundle\FrontendBundle\Entity\Route $route = null)
    {
        $this->route = $route;
        $this->setRouteId($route->getId());

        return $this;
    }

    /**
     * Get route
     *
     * @return \TB\Bundle\FrontendBundle\Entity\Route 
     */
    public function getRoute()
    {
        return $this->route;
    }
    
    /**
     * Returns an data array representatiosn of this entity for the activity feed
     */
    public function exportAsActivity()
    {   
        $data = [
            'url' => '/campaign/' . $this->getCampaign()->getSlug() . '/trail/' . $this->getRoute()->getSlug(),
            'objectType' => 'campaignTrail',
            'id' => ['campaign_id' => $this->getCampaignId(), 'route_id' => $this->getRouteId()],
            'displayName' => $this->getRoute()->getName(),
        ];
        
        return $data;
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {

    }

}
