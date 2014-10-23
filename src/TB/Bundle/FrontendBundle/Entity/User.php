<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Gedmo\Mapping\Annotation as Gedmo;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"user" = "UserProfile", "brand" = "BrandProfile"})
 */
abstract class User extends BaseUser implements Exportable
{

    const GENDER_NONE = 0;
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    
    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"firstName", "lastName"}, updatable=false, separator="")
     * @ORM\Column(name="name", type="string", length=50, nullable=true, unique=true)
     */
    protected $name;
    
    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=50)
     * @Assert\NotBlank()
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=50)
     * @Assert\NotBlank()
     */
    private $lastName;

    /**
     * @var Point
     *
     * @ORM\Column(name="location", type="point", columnDefinition="GEOMETRY(POINT,4326)")
     * @Assert\NotBlank()
     */
    private $location; 
    
    /**
     * @var string
     *
     * @ORM\Column(name="about", type="text", nullable=true)
     */
    private $about;
    
    /**
     * @var string
     *
     * @ORM\Column(name="synopsis", type="text", nullable=true)
     */
    private $synopsis;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", length=100, nullable=true)
     */
    private $avatar;
    
    /**
     * @var string
     *
     * @ORM\Column(name="avatar_gravatar", type="string", length=100, nullable=true)
     */
    private $avatarGravatar;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="activity_unseen_count", type="smallint", nullable=true)
     */
    private $activityUnseenCount;
    
    /**
     * @var datetime
     *
     * @ORM\Column(name="activity_last_viewed", type="datetime", nullable=true)
     */
    private $activityLastViewed;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Route", mappedBy="user")
     **/
    private $routes;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="user")
     **/
    private $events;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Editorial", mappedBy="user")
     **/
    private $editorials;
    
    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="userIFollow")
     **/
    private $myFollower;    

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="myFollower")
     * @ORM\JoinTable(name="follower",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="follower_user_id", referencedColumnName="id")}
     *      )
     **/
    private $userIFollow;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="RoutePublishActivity", mappedBy="actor")
     **/
    private $routePublishActivities;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="RouteLikeActivity", mappedBy="actor")
     **/
    private $routeLikeActivities;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="RouteUndoLikeActivity", mappedBy="actor")
     **/
    private $routeUndoLikeActivities;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="UserFollowActivity", mappedBy="actor")
     **/
    private $userFollowActivities;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="UserFollowActivity", mappedBy="object")
     **/
    private $userFollowedActivities;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="UserUnfollowActivity", mappedBy="actor")
     **/
    private $userUnfollowActivities;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="UserUnfollowActivity", mappedBy="object")
     **/
    private $userUnfollowedActivities;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="homepage_order", type="smallint", nullable=true)
     */
    private $homepageOrder;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="UserActivity", mappedBy="user")
     **/
    private $userActivities;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="TB\Bundle\FrontendBundle\Entity\RouteLike", mappedBy="user")
     */
    private $routeLikes;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="gender", type="smallint")
     */
    private $gender = 0;    
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="newsletter", type="boolean", options={"default" = true})
     */
    private $newsletter = true;
    
    /**
     * @var datetime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="registered_at", type="datetime")
     */
    protected $registeredAt;
    
    /**
     * @var UserRegisterActivity
     *
     * @ORM\OneToOne(targetEntity="UserRegisterActivity", mappedBy="actor")
     **/
    private $userRegisterActivity;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Campaign", mappedBy="user")
     **/
    private $campaigns;
    
    /**
     * @ORM\ManyToMany(targetEntity="Campaign", inversedBy="follower")
     * @ORM\JoinTable(name="campaign_follower")
     **/
    private $campaignsIFollow;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="CampaignFollowActivity", mappedBy="actor")
     **/
    private $campaignFollowActivities;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="CampaignUnfollowActivity", mappedBy="actor")
     **/
    private $campaignUnfollowActivities;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->routes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
        $this->editorials = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userIFollow = new \Doctrine\Common\Collections\ArrayCollection();
        $this->campaignsIFollow = new \Doctrine\Common\Collections\ArrayCollection();
        $this->myFollower = new \Doctrine\Common\Collections\ArrayCollection();
        $this->routeLikeActivities = new \Doctrine\Common\Collections\ArrayCollection();
        $this->routeUndoLikeActivities = new \Doctrine\Common\Collections\ArrayCollection();
        $this->campaignFollowActivities = new \Doctrine\Common\Collections\ArrayCollection();
        $this->campaignUnfollowActivities = new \Doctrine\Common\Collections\ArrayCollection();
        parent::__construct();
    }
    
    abstract public function getTitle();

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set about
     *
     * @param string $about
     * @return User
     */
    public function setAbout($about)
    {
        $this->about = $about;

        return $this;
    }

    /**
     * Get about
     *
     * @return string 
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * Set synopsis
     *
     * @param string $synopsis
     * @return User
     */
    public function setSynopsis($synopsis)
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    /**
     * Get synopsis
     *
     * @return string 
     */
    public function getSynopsis()
    {
        return $this->synopsis;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string 
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Add routes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $routes
     * @return User
     */
    public function addRoute(\TB\Bundle\FrontendBundle\Entity\Route $routes)
    {
        $this->routes[] = $routes;

        return $this;
    }

    /**
     * Remove routes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $routes
     */
    public function removeRoute(\TB\Bundle\FrontendBundle\Entity\Route $routes)
    {
        $this->routes->removeElement($routes);
    }

    /**
     * Get routes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Add events
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Event $events
     * @return User
     */
    public function addEvent(\TB\Bundle\FrontendBundle\Entity\Event $events)
    {
        $this->events[] = $events;

        return $this;
    }

    /**
     * Remove events
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Event $events
     */
    public function removeEvent(\TB\Bundle\FrontendBundle\Entity\Event $events)
    {
        $this->events->removeElement($events);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Add editorials
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Editorial $editorials
     * @return User
     */
    public function addEditorial(\TB\Bundle\FrontendBundle\Entity\Editorial $editorials)
    {
        $this->editorials[] = $editorials;

        return $this;
    }

    /**
     * Remove editorials
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Editorial $editorials
     */
    public function removeEditorial(\TB\Bundle\FrontendBundle\Entity\Editorial $editorials)
    {
        $this->editorials->removeElement($editorials);
    }

    /**
     * Get editorials
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEditorials()
    {
        return $this->editorials;
    }
    

    /**
     * Sets the email as username. The email is the username in our application
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->setUsername($email);

        return parent::setEmail($email);
    }

    /**
     * Sets the canonical email as username.
     *
     * @param string $emailCanonical
     * @return User
     */
    public function setEmailCanonical($emailCanonical)
    {
        $this->setUsernameCanonical($emailCanonical);

        return parent::setEmailCanonical($emailCanonical);
    }
    
    /**
     * Set name
     *
     * @param string $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set lastName
     *
     * @param string $lastName
     * @return UserProfile
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set location. Create a Point Object when the location is a string
     *
     * @param point $location
     * @return UserProfile
     */
    public function setLocation($location)
    {
        if (is_string($location)) {
            // check the location Sting format
            if (!preg_match('/^\(([-\d]+\.[-\d]+), ([-\d]+\.[-\d]+)\)$/', $location, $match)) {
                throw new \Exception(sprintf('Invalid location string format: %s', $location));
            }
            $location = new Point($match[1], $match[2], 4326);
        }
        
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return point 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return UserProfile
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->firstName;
    }
    
    public function getFullName()
    {
        return sprintf('%s %s', $this->getFirstName(), $this->getLastName());
    }


    /**
     * Set avatarGravatar
     *
     * @param string $avatarGravatar
     * @return User
     */
    public function setAvatarGravatar($avatarGravatar)
    {
        $this->avatarGravatar = $avatarGravatar;

        return $this;
    }

    /**
     * Get avatarGravatar
     *
     * @return string 
     */
    public function getAvatarGravatar()
    {
        return $this->avatarGravatar;
    }
    
    /**
     * Get the avatar from gravatar
     */
    public function updateAvatarGravatar()
    {
        if ($this->getEmail() == '') {
            throw new \Exception('Unable to generate gravatar profile hash, missing email firld for User');
        }
        $hash = md5($this->getEmail());
        $imageUrl = sprintf('http://www.gravatar.com/avatar/%s', $hash);
        $testImageUrl = $imageUrl . '?d=404'; //forces Gravatar to return 404 status code for none existing images
        
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'HEAD');
        curl_setopt($c, CURLOPT_HEADER, 1);
        curl_setopt($c, CURLOPT_NOBODY, true);
        curl_setopt($c, CURLOPT_URL, $testImageUrl);
        curl_exec($c);
        $statusCode = curl_getinfo($c, CURLINFO_HTTP_CODE);
        
        if ($statusCode === 200) {
            $this->setAvatarGravatar($imageUrl);
        } else {
            $this->setAvatarGravatar('');
        }
    }

    /**
     * Add userIFollow
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $userIFollow
     * @return User
     */
    public function addUserIFollow(\TB\Bundle\FrontendBundle\Entity\User $userIFollow)
    {
        $this->userIFollow[] = $userIFollow;

        return $this;
    }

    /**
     * Remove userIFollow
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $userIFollow
     */
    public function removeUserIFollow(\TB\Bundle\FrontendBundle\Entity\User $userIFollow)
    {
        $this->userIFollow->removeElement($userIFollow);
    }

    /**
     * Get userIFollow
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserIFollow()
    {
        return $this->userIFollow;
    }

    /**
     * Add myFollower
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $myFollower
     * @return User
     */
    public function addMyFollower(\TB\Bundle\FrontendBundle\Entity\User $myFollower)
    {
        $myFollower->addUserIFollow($this);
        $this->myFollower[] = $myFollower;

        return $this;
    }

    /**
     * Remove myFollower
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $myFollower
     */
    public function removeMyFollower(\TB\Bundle\FrontendBundle\Entity\User $myFollower)
    {
        $this->myFollower->removeElement($myFollower);
    }

    /**
     * Get myFollower
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMyFollower()
    {
        return $this->myFollower;
    }
    
    /**
     * Checks if the User is following a given User
     *
     * @param User $user The User to check in the follower
     * @return boolean returns true if the User is following, false if not
     */
    public function isFollowingUser(User $user)
    {
        foreach ($this->getUserIFollow() as $userIFollowUser) {
            if ($userIFollowUser->getId() === $user->getId()) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Checks if the User is following a given Campaign
     *
     * @param Campaign $campaign The Campaign to check in the follower
     * @return boolean returns true if the User is following, false if not
     */
    public function isFollowingCampaign(Campaign $campaign)
    {
        foreach ($this->getCampaignsIFollow() as $campaignIFollow) {
            if ($campaignIFollow->getId() === $campaign->getId()) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Add routePublishActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\RoutePublishActivity $routePublishActivities
     * @return User
     */
    public function addRoutePublishActivity(\TB\Bundle\FrontendBundle\Entity\RoutePublishActivity $routePublishActivities)
    {
        $this->routePublishActivities[] = $routePublishActivities;

        return $this;
    }

    /**
     * Remove routePublishActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\RoutePublishActivity $routePublishActivities
     */
    public function removeRoutePublishActivity(\TB\Bundle\FrontendBundle\Entity\RoutePublishActivity $routePublishActivities)
    {
        $this->routePublishActivities->removeElement($routePublishActivities);
    }

    /**
     * Get routePublishActivities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRoutePublishActivities()
    {
        return $this->routePublishActivities;
    }

    /**
     * Add userFollowActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\UserFollowActivity $userFollowActivities
     * @return User
     */
    public function addUserFollowActivity(\TB\Bundle\FrontendBundle\Entity\UserFollowActivity $userFollowActivities)
    {
        $this->userFollowActivities[] = $userFollowActivities;

        return $this;
    }

    /**
     * Remove userFollowActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\UserFollowActivity $userFollowActivities
     */
    public function removeUserFollowActivity(\TB\Bundle\FrontendBundle\Entity\UserFollowActivity $userFollowActivities)
    {
        $this->userFollowActivities->removeElement($userFollowActivities);
    }

    /**
     * Get userFollowActivities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserFollowActivities()
    {
        return $this->userFollowActivities;
    }

    /**
     * Add userFollowedActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\UserFollowActivity $userFollowedActivities
     * @return User
     */
    public function addUserFollowedActivity(\TB\Bundle\FrontendBundle\Entity\UserFollowActivity $userFollowedActivities)
    {
        $this->userFollowedActivities[] = $userFollowedActivities;

        return $this;
    }

    /**
     * Remove userFollowedActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\UserFollowActivity $userFollowedActivities
     */
    public function removeUserFollowedActivity(\TB\Bundle\FrontendBundle\Entity\UserFollowActivity $userFollowedActivities)
    {
        $this->userFollowedActivities->removeElement($userFollowedActivities);
    }

    /**
     * Get userFollowedActivities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserFollowedActivities()
    {
        return $this->userFollowedActivities;
    }

    /**
     * Add userUnfollowActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\UserUnfollowActivity $userUnfollowActivities
     * @return User
     */
    public function addUserUnfollowActivity(\TB\Bundle\FrontendBundle\Entity\UserUnfollowActivity $userUnfollowActivities)
    {
        $this->userUnfollowActivities[] = $userUnfollowActivities;

        return $this;
    }

    /**
     * Remove userUnfollowActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\UserUnfollowActivity $userUnfollowActivities
     */
    public function removeUserUnfollowActivity(\TB\Bundle\FrontendBundle\Entity\UserUnfollowActivity $userUnfollowActivities)
    {
        $this->userUnfollowActivities->removeElement($userUnfollowActivities);
    }

    /**
     * Get userUnfollowActivities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserUnfollowActivities()
    {
        return $this->userUnfollowActivities;
    }

    /**
     * Add userUnfollowedActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\UserUnfollowActivity $userUnfollowedActivities
     * @return User
     */
    public function addUserUnfollowedActivity(\TB\Bundle\FrontendBundle\Entity\UserUnfollowActivity $userUnfollowedActivities)
    {
        $this->userUnfollowedActivities[] = $userUnfollowedActivities;

        return $this;
    }

    /**
     * Remove userUnfollowedActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\UserUnfollowActivity $userUnfollowedActivities
     */
    public function removeUserUnfollowedActivity(\TB\Bundle\FrontendBundle\Entity\UserUnfollowActivity $userUnfollowedActivities)
    {
        $this->userUnfollowedActivities->removeElement($userUnfollowedActivities);
    }

    /**
     * Get userUnfollowedActivities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserUnfollowedActivities()
    {
        return $this->userUnfollowedActivities;
    }
    
    public function export()
    {   
        if ($this instanceof \TB\Bundle\FrontendBundle\Entity\UserProfile) {
            $discr = 'user';
        } elseif ($this instanceof \TB\Bundle\FrontendBundle\Entity\BrandProfile) {
            $discr = 'brand';
        }
        
        $data = [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'avatar' => $this->getAvatarUrl(),
            'type' => $discr,
        ];

        return $data;
    }

    /**
     * Set activityLastViewed
     *
     * @param \DateTime $activityLastViewed
     * @return User
     */
    public function setActivityLastViewed($activityLastViewed)
    {
        $this->activityLastViewed = $activityLastViewed;

        return $this;
    }

    /**
     * Get activityLastViewed
     *
     * @return \DateTime 
     */
    public function getActivityLastViewed()
    {
        return $this->activityLastViewed;
    }
    
    /**
     * Gets the best avatar for this user
     */
    public function getAvatarUrl()
    {
        if ($this->getAvatar()) {
            $url = sprintf('http://assets.trailburning.com/images/profile/%s/%s', $this->getName(), $this->getAvatar());
        } elseif ($this->getAvatarGravatar()) {
            $url = $this->getAvatarGravatar();
        } elseif ($this instanceof \TB\Bundle\FrontendBundle\Entity\BrandProfile) {
            $url = null;
        } else {
            if ($this->getGender() === User::GENDER_FEMALE) {
                $url = 'http://assets.trailburning.com/images/icons/avatars/avatar_woman.jpg';
            } else {
                $url = 'http://assets.trailburning.com/images/icons/avatars/avatar_man.jpg';
            }
        }
        
        return $url;
    }

    /**
     * Add userActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\UserActivity $userActivities
     * @return User
     */
    public function addUserActivity(\TB\Bundle\FrontendBundle\Entity\UserActivity $userActivities)
    {
        $this->userActivities[] = $userActivities;

        return $this;
    }

    /**
     * Remove userActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\UserActivity $userActivities
     */
    public function removeUserActivity(\TB\Bundle\FrontendBundle\Entity\UserActivity $userActivities)
    {
        $this->userActivities->removeElement($userActivities);
    }

    /**
     * Get userActivities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserActivities()
    {
        return $this->userActivities;
    }

    /**
     * Set activityUnseenCount
     *
     * @param integer $activityUnseenCount
     * @return User
     */
    public function setActivityUnseenCount($activityUnseenCount)
    {
        $this->activityUnseenCount = $activityUnseenCount;

        return $this;
    }

    /**
     * Get activityUnseenCount
     *
     * @return integer 
     */
    public function getActivityUnseenCount()
    {
        return $this->activityUnseenCount;
    }

    /**
     * Add routeLikes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $routeLikes
     * @return User
     */
    public function addRouteLike(\TB\Bundle\FrontendBundle\Entity\Route $routeLikes)
    {
        $this->routeLikes[] = $routeLikes;

        return $this;
    }

    /**
     * Remove routeLikes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $routeLikes
     */
    public function removeRouteLike(\TB\Bundle\FrontendBundle\Entity\Route $routeLikes)
    {
        $this->routeLikes->removeElement($routeLikes);
    }

    /**
     * Get routeLikes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRouteLikes()
    {
        return $this->routeLikes;
    }

    /**
     * Add routeLikeActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\RouteLikeActivity $routeLikeActivities
     * @return User
     */
    public function addRouteLikeActivity(\TB\Bundle\FrontendBundle\Entity\RouteLikeActivity $routeLikeActivities)
    {
        $this->routeLikeActivities[] = $routeLikeActivities;

        return $this;
    }

    /**
     * Remove routeLikeActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\RouteLikeActivity $routeLikeActivities
     */
    public function removeRouteLikeActivity(\TB\Bundle\FrontendBundle\Entity\RouteLikeActivity $routeLikeActivities)
    {
        $this->routeLikeActivities->removeElement($routeLikeActivities);
    }

    /**
     * Get routeLikeActivities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRouteLikeActivities()
    {
        return $this->routeLikeActivities;
    }

    /**
     * Add routeUndoLikeActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\RouteUndoLikeActivity $routeUndoLikeActivities
     * @return User
     */
    public function addRouteUndoLikeActivity(\TB\Bundle\FrontendBundle\Entity\RouteUndoLikeActivity $routeUndoLikeActivities)
    {
        $this->routeUndoLikeActivities[] = $routeUndoLikeActivities;

        return $this;
    }

    /**
     * Remove routeUndoLikeActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\RouteUndoLikeActivity $routeUndoLikeActivities
     */
    public function removeRouteUndoLikeActivity(\TB\Bundle\FrontendBundle\Entity\RouteUndoLikeActivity $routeUndoLikeActivities)
    {
        $this->routeUndoLikeActivities->removeElement($routeUndoLikeActivities);
    }

    /**
     * Get routeUndoLikeActivities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRouteUndoLikeActivities()
    {
        return $this->routeUndoLikeActivities;
    }
    
    public function setHomepageOrder($homepageOrder)
    {
        $this->homepageOrder = $homepageOrder;

        return $this;
    }

    /**
     * Get homepageOrder
     *
     * @return integer 
     */
    public function getHomepageOrder()
    {
        return $this->homepageOrder;
    }

    /**
     * Set gender
     *
     * @param integer $gender
     * @return User
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return integer 
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set newsletter
     *
     * @param boolean $newsletter
     * @return User
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * Get newsletter
     *
     * @return boolean 
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Set registeredAt
     *
     * @param \DateTime $registeredAt
     * @return User
     */
    public function setRegisteredAt($registeredAt)
    {
        $this->registeredAt = $registeredAt;

        return $this;
    }

    /**
     * Get registeredAt
     *
     * @return \DateTime 
     */
    public function getRegisteredAt()
    {
        return $this->registeredAt;
    }

    /**
     * Set userRegisterActivity
     *
     * @param \TB\Bundle\FrontendBundle\Entity\UserRegisterActivity $userRegisterActivity
     * @return User
     */
    public function setUserRegisterActivity(\TB\Bundle\FrontendBundle\Entity\UserRegisterActivity $userRegisterActivity = null)
    {
        $this->userRegisterActivity = $userRegisterActivity;

        return $this;
    }

    /**
     * Get userRegisterActivity
     *
     * @return \TB\Bundle\FrontendBundle\Entity\UserRegisterActivity 
     */
    public function getUserRegisterActivity()
    {
        return $this->userRegisterActivity;
    }

    /**
     * Add campaigns
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Campaign $campaigns
     * @return User
     */
    public function addCampaign(\TB\Bundle\FrontendBundle\Entity\Campaign $campaigns)
    {
        $this->campaigns[] = $campaigns;

        return $this;
    }

    /**
     * Remove campaigns
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Campaign $campaigns
     */
    public function removeCampaign(\TB\Bundle\FrontendBundle\Entity\Campaign $campaigns)
    {
        $this->campaigns->removeElement($campaigns);
    }

    /**
     * Get campaigns
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCampaigns()
    {
        return $this->campaigns;
    }

    /**
     * Add campaignsIFollow
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Campaign $campaignsIFollow
     * @return User
     */
    public function addCampaignsIFollow(\TB\Bundle\FrontendBundle\Entity\Campaign $campaignsIFollow)
    {
        $this->campaignsIFollow[] = $campaignsIFollow;

        return $this;
    }

    /**
     * Remove campaignsIFollow
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Campaign $campaignsIFollow
     */
    public function removeCampaignsIFollow(\TB\Bundle\FrontendBundle\Entity\Campaign $campaignsIFollow)
    {
        $this->campaignsIFollow->removeElement($campaignsIFollow);
    }

    /**
     * Get campaignsIFollow
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCampaignsIFollow()
    {
        return $this->campaignsIFollow;
    }

    /**
     * Add campaignFollowActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\CampaignFollowActivity $campaignFollowActivities
     * @return User
     */
    public function addCampaignFollowActivity(\TB\Bundle\FrontendBundle\Entity\CampaignFollowActivity $campaignFollowActivities)
    {
        $this->campaignFollowActivities[] = $campaignFollowActivities;

        return $this;
    }

    /**
     * Remove campaignFollowActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\CampaignFollowActivity $campaignFollowActivities
     */
    public function removeCampaignFollowActivity(\TB\Bundle\FrontendBundle\Entity\CampaignFollowActivity $campaignFollowActivities)
    {
        $this->campaignFollowActivities->removeElement($campaignFollowActivities);
    }

    /**
     * Get campaignFollowActivities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCampaignFollowActivities()
    {
        return $this->campaignFollowActivities;
    }

    /**
     * Add campaignUnfollowActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\CampaignUnfollowActivity $campaignUnfollowActivities
     * @return User
     */
    public function addCampaignUnfollowActivity(\TB\Bundle\FrontendBundle\Entity\CampaignUnfollowActivity $campaignUnfollowActivities)
    {
        $this->campaignUnfollowActivities[] = $campaignUnfollowActivities;

        return $this;
    }

    /**
     * Remove campaignUnfollowActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\CampaignUnfollowActivity $campaignUnfollowActivities
     */
    public function removeCampaignUnfollowActivity(\TB\Bundle\FrontendBundle\Entity\CampaignUnfollowActivity $campaignUnfollowActivities)
    {
        $this->campaignUnfollowActivities->removeElement($campaignUnfollowActivities);
    }

    /**
     * Get campaignUnfollowActivities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCampaignUnfollowActivities()
    {
        return $this->campaignUnfollowActivities;
    }
}
