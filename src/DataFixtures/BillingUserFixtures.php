<?php
namespace App\DataFixtures;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\BillingUser;
class BillingUserFixtures extends Fixture
{
    private $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    public function load(ObjectManager $manager)
    {
        $userEmails = ['simpleUser@gmail.com', 'adminUser@gmail.com'];
        $userRoles = [['ROLE_USER'], ['ROLE_SUPER_ADMIN']];
        $userPasswords = ['passwordForSimpleUser', 'passwordForAdminUser'];
        for ($i = 0; $i < 2; $i++) {
            $billingUser = new BillingUser();
            $billingUser->setEmail($userEmails[$i]);
            $billingUser->setRoles($userRoles[$i]);
            $billingUser->setPassword($this->passwordEncoder->encodePassword($billingUser, $userPasswords[$i]));
            $manager->persist($billingUser);
        }
        $manager->flush();
    }
}