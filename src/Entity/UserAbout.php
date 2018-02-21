<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsersAbout
 *
 * @ORM\Table(name="users_about", indexes={@ORM\Index(name="user", columns={"user"}), @ORM\Index(name="user_item_value", columns={"user", "item", "value"}), @ORM\Index(name="item", columns={"item"})})
 * @ORM\Entity
 */
class UserAbout
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
     * @ORM\Column(name="up_date", type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $upDate;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    private $user;

    /**
     * Only for unit test
     * User constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $method = sprintf("set%s", ucfirst($key));
            if (method_exists($this, $method)) {
                call_user_func([$this, $method], $value);
            }
        }
    }

    /**
     * @return string
     */
    public function getItem(): string
    {
        return $this->item;
    }

    /**
     * @param string $item
     */
    public function setItem(string $item): void
    {
        $this->item = $item;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}
