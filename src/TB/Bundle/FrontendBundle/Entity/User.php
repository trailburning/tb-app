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
abstract class User extends BaseUser
{
    
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
     * @ORM\ManyToMany(targetEntity="User", mappedBy="iFollow")
     **/
    private $myFollower;    

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="myFollower")
     * @ORM\JoinTable(name="follower",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="follower_user_id", referencedColumnName="id")}
     *      )
     **/
    private $iFollow;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="RoutePublishActivity", mappedBy="routePublishActivities")
     **/
    private $routePublishActivities;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="UserFollowActivity", mappedBy="userFollowActivities")
     **/
    private $userFollowActivities;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="UserFollowActivity", mappedBy="userFollowedActivities")
     **/
    private $userFollowedActivities;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="UserUnfollowActivity", mappedBy="userUnfollowActivities")
     **/
    private $userUnfollowActivities;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="UserUnfollowActivity", mappedBy="userUnfollowedActivities")
     **/
    private $userUnfollowedActivities;
    
    /**
     * @var datetime
     *
     * @ORM\Column(name="activity_last_viewed", type="datetime", nullable=true)
     */
    protected $activityLastViewed;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->routes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
        $this->editorials = new \Doctrine\Common\Collections\ArrayCollection();
        $this->iFollow = new \Doctrine\Common\Collections\ArrayCollection();
        $this->myFollower = new \Doctrine\Common\Collections\ArrayCollection();
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
            throw new Exception('Unable to generate gravatar profile hash, missing email firld for User');
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
     * Add iFollow
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $iFollow
     * @return User
     */
    public function addIFollow(\TB\Bundle\FrontendBundle\Entity\User $iFollow)
    {
        $this->iFollow[] = $iFollow;

        return $this;
    }

    /**
     * Remove iFollow
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $iFollow
     */
    public function removeIFollow(\TB\Bundle\FrontendBundle\Entity\User $iFollow)
    {
        $this->iFollow->removeElement($iFollow);
    }

    /**
     * Get iFollow
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getIFollow()
    {
        return $this->iFollow;
    }

    /**
     * Add myFollower
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $myFollower
     * @return User
     */
    public function addMyFollower(\TB\Bundle\FrontendBundle\Entity\User $myFollower)
    {
        $myFollower->addIFollow($this);
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
     * Tests if the User is following a given User
     *
     * @param User $user The User to looup in the follower
     * @return boolean returns true if the User is following, false if not
     */
    public function isFollowing(User $user)
    {
        foreach ($this->getIFollow() as $iFollowUser) {
            if ($iFollowUser->getId() === $user->getId()) {
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
}
