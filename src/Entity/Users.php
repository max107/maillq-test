<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Users
 *
 * @ORM\Table(name="users", uniqueConstraints={@ORM\UniqueConstraint(name="email", columns={"email"})}, indexes={@ORM\Index(name="email_password", columns={"email", "password"}), @ORM\Index(name="role_reg_date", columns={"role", "reg_date"})})
 * @ORM\Entity
 */
class Users
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=100, nullable=false)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=100, nullable=false)
     */
    private $role;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="reg_date", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $regDate = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="last_visit", type="datetime", nullable=true)
     */
    private $lastVisit;


}
