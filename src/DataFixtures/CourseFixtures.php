<?php
namespace App\DataFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Course;
use App\Entity\Transaction;
class CourseFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        /**
         * Course types: 0 - rent, 1 - buy, 2 - free
         */
        $courseCode = ['vvedenie-v-javascript', 'osnovy-javascript', 'java-dlya-nachinayushchih', 'kachestvo-koda', 'struktury-dannyh'];
        $courseType = [RENT, BUY, FREE, RENT, BUY];
        $coursePrice = [25.55, 20.25, 0.0, 35.45, 23,85];
        /**
         * Transaction types: 0 - payment, 1 - deposit
         */
        $transactionSender = [9, 11, 14];
        $transactionForCourse = ['vvedenie-v-javascript', 'osnovy-javascript', 'java-dlya-nachinayushchih', 'kachestvo-koda', 'struktury-dannyh'];
        $transactionType = [PAYMENT, PAYMENT, DEPOSIT, PAYMENT, PAYMENT];
        $transactionValue = [250.2, 300.55, 275.45, 350.9, 260.55];
        $transactionExpireAt = [(new \DateTime())->modify('+1 month'), (new \DateTime())->modify('+1 day'), (new \DateTime())->modify('+1 hour')];
        for ($i = 0; $i < 5; $i++) {
            $course = new Course();
            $course->setCode($courseCode[$i]);
            $course->setType($courseType[$i]);
            $course->setPrice($coursePrice[$i]);
            $manager->persist($course);
        }
        $manager->flush();
        $courses = $manager->getRepository(Course::class)->findAll();
        for ($i = 0; $i < 5; $i++) {
            $course = $manager->getRepository(Course::class)->find($courses[$i]->getId());
            $transaction = new Transaction();
            $transaction->setCreatedAt((new \DateTime()));
            $transaction->setUserId($transactionSender[$i]);
            $transaction->setCourse($course);
            $transaction->setType($transactionType[$i]);
            $transaction->setValue($transactionValue[$i]);
            $transaction->setExpireAt($transactionExpireAt[$i]);
            $manager->persist($transaction);
        }
        $manager->flush();
    }
}