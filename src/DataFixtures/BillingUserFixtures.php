<?php

namespace App\DataFixtures;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\BillingUser;
use App\Entity\Course;
use App\Entity\Transaction;
use App\Service\PaymentService;

class BillingUserFixtures extends Fixture
{
    const PAYMENT_TYPE = 0;
    const DEPOSIT_TYPE = 1;

    const RENT_COURSE = 0;
    const BUY_COURSE = 1;
    const FREE_COURSE = 2;

    private $passwordEncoder;
    private $paymentService;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, PaymentService $paymentService)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->paymentService = $paymentService;
    }

    public function load(ObjectManager $manager)
    {
        $userEmails = ['simpleUser@gmail.com', 'adminUser@gmail.com', 'aaa@mail.ru'];
        $userRoles = [['ROLE_USER'], ['ROLE_SUPER_ADMIN'], ['ROLE_USER','ROLE_SUPER_ADMIN']];
        $userPasswords = ['passwordForSimpleUser', 'passwordForAdminUser', '123456'];
        $userBalance = [500, 100, 5000];
        $courseTitle = ['Введение в JavaScript',
            'Основы JavaScript',
            'Java для начинающих',
            'Качество кода',
            'Структуры данных'];
        $courseCode = ['vvedenie-v-javascript', 'osnovy-javascript', 'java-dlya-nachinayushchih', 'kachestvo-koda', 'struktury-dannyh'];
        $courseType = [self::RENT_COURSE, self::BUY_COURSE, self::FREE_COURSE, self::RENT_COURSE, self::BUY_COURSE];
        $coursePrice = [25.55, 20.25, 0.0, 35.45, 5000.0];
        $transactionForCourse = ['vvedenie-v-javascript', 'osnovy-javascript', 'java-dlya-nachinayushchih', 'kachestvo-koda', 'struktury-dannyh'];
        $transactionType = [self::PAYMENT_TYPE, self::PAYMENT_TYPE, self::PAYMENT_TYPE, self::PAYMENT_TYPE];
        $transactionValue = [25.55, 20.25, 35.45, 5000];
        $transactionExpireAt = [(new \DateTime())->modify('+1 hour'), (new \DateTime())->modify('-1 day'), (new \DateTime())->modify('-1 week')];
        for ($i = 0; $i < 3; $i++) {
            $billingUser = new BillingUser();
            $billingUser->setEmail($userEmails[$i]);
            $billingUser->setRoles($userRoles[$i]);
            $billingUser->setBalance($userBalance[$i]);
            $billingUser->setPassword($this->passwordEncoder->encodePassword($billingUser, $userPasswords[$i]));
            $manager->persist($billingUser);
            $manager->flush();
            $this->paymentService->depositTransaction($billingUser->getId());
        }
        $users = $manager->getRepository(BillingUser::class)->findAll();
        $transactionSender = [];
        foreach ($users as $user) {
            array_push($transactionSender, $user->getId());
        }
        for ($i = 0; $i < 5; $i++) {
            $course = new Course();
            $course->setTitle($courseTitle[$i]);
            $course->setCode($courseCode[$i]);
            $course->setType($courseType[$i]);
            $course->setPrice($coursePrice[$i]);
            $manager->persist($course);
        }
        $manager->flush();
        $courses = $manager->getRepository(Course::class)->findAll();
        for ($i = 0; $i < 3; $i++) {
            $course = $manager->getRepository(Course::class)->find($courses[$i]->getId());
            $transaction = new Transaction();
            $transaction->setCreatedAt((new \DateTime()));
            $transaction->setUserId($transactionSender[0]);
            $transaction->setCourse($course);
            $transaction->setType($transactionType[$i]);
            $transaction->setValue($transactionValue[$i]);
            $transaction->setExpireAt($transactionExpireAt[$i]);
            $manager->persist($transaction);
        }
        $manager->flush();
        for ($i = 0; $i < 2; $i++) {
            $course = $manager->getRepository(Course::class)->find($courses[$i]->getId());
            $transaction = new Transaction();
            $transaction->setCreatedAt((new \DateTime()));
            $transaction->setUserId($transactionSender[2]);
            $transaction->setCourse($course);
            $transaction->setType($transactionType[0]);
            $transaction->setValue($transactionValue[0]);
            $transaction->setExpireAt((new \DateTime())->modify('+5 hours'));
            $manager->persist($transaction);
        }
        $manager->flush();
    }
}