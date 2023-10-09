<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private SluggerInterface $slugger
    ){}

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        for($i = 0 ; $i < 6; $i++){
            $user = new User();
            $user
                ->setEmail($faker->email)
                ->setLastname($faker->lastName)
                ->setFirstname($faker->firstName)
                ->setAddress($faker->streetAddress)
                ->setZipcode(str_replace(' ', '', $faker->postcode))
                ->setCity($faker->city)
                ->setIsVerified(true)
            ;
            if($i === 0){ //First fake user is a fake admin.
                $user
                    ->setRoles(['ROLE_ADMIN'])
                    ->setPassword(
                        $this->passwordHasher->hashPassword($user, 'Adminer1=')
                    )
                ;
            }
            else{
                $user->setPassword(
                    $this->passwordHasher->hashPassword($user, 'secret')
                );
            }
            $manager->persist($user);
        };
        $manager->flush();
    }
}