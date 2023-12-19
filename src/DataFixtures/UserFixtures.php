<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $pwd = 'test';

        $users =  [
            [
                'email' => 'user@user.fr',
                'role' => ['ROLE_USER'],
            ],
            [
                'email' => 'admin@user.fr',
                'role' => ['ROLE_ADMIN'],
            ]
        ];

        $companies = $manager->getRepository(Company::class)->findAll();

        //dd($companies);

        foreach ($users as $user) {
            $object = (new User())
                ->setEmail($user['email'])
                ->setName($faker->name)
                ->setLastname($faker->lastName)
                ->setRoles($user['role'])
                ->setCompany($companies[array_rand($companies)])
            ;
            $object->setPassword($this->passwordHasher->hashPassword($object, $pwd));
            $manager->persist($object);
        }

        for ($i = 0; $i < 10; $i++) {
            $user = (new User())
                ->setEmail($faker->email)
                ->setName($faker->name)
                ->setLastname($faker->lastName)
                ->setRoles([])
                ->setCompany($companies[array_rand($companies)])
            ;
            $user->setPassword($this->passwordHasher->hashPassword($user, $pwd));
            $manager->persist($user);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CompanyFixtures::class,
        ];
    }

}