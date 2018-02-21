<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsersAbout
 *
 * @ORM\Table(name="users_about", indexes={@ORM\Index(name="user", columns={"user"}), @ORM\Index(name="user_item_value", columns={"user", "item", "value"}), @ORM\Index(name="item", columns={"item"})})
 * @ORM\Entity
 */
class UsersAbout
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="item", type="string", nullable=false)
     */
    private $item;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=250, nullable=false)
     */
    private $value;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="up_date", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $upDate = 'CURRENT_TIMESTAMP';

    /**
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id")
     * })
     */
    private $user;


}
