<?php

namespace App\DTO;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\UniqueUser;

class BillingUserDTO
{
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="Enter email")
     * @Assert\Email(message="Invalid email")
     * @UniqueUser()
     */
    public $email;
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="Enter password")
     * @Assert\Length(min = 6, minMessage="Password must be at least 6 characters")
     */
    public $password;
}