<?php

namespace Sensio\Bundle\GeneratorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Profile
 *
 * @ORM\Table(name="Profile")
 * @ORM\Entity(repositoryClass="Sensio\Bundle\GeneratorBundle\Entity\ProfileRepository")
 */
class Profile
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", precision=0, scale=0, nullable=false, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="Profile_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, precision=0, scale=0, nullable=false, unique=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, precision=0, scale=0, nullable=false, unique=false)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, precision=0, scale=0, nullable=false, unique=false)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", length=255, precision=0, scale=0, nullable=false, unique=false)
     */
    private $avatar;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=255, precision=0, scale=0, nullable=false, unique=false)
     */
    private $location;

    /**
     * @var string
     *
     * @ORM\Column(name="about", type="string", length=255, precision=0, scale=0, nullable=false, unique=false)
     */
    private $about;


}
