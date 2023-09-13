<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->makeUser('Pierre', 'Lacaud', 'me@me.com', 'password'));
        $manager->persist($this->makeUser('Didier', 'Rapota', 'me@me.com', 'password'));
        $manager->persist($this->makeUser('Jean', 'Kapoli', 'me@me.com', 'password'));
        $manager->persist($this->makeUser('Eric', 'Sigora', 'me@me.com', 'password'));

        $manager->flush();
    }

    public function makeUser(string $firstname, string $lastname, string $email, string $plainPassword): User
    {
        $user = new User();
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setEmail($email);
        $factory = new PasswordHasherFactory(['common' => ['algorithm' => 'bcrypt']]);
        $hasher = $factory->getPasswordHasher('common');
        $user->setPassword($hasher->hash($plainPassword));
        $user->setClient($this->getReference('client'));

        return $user;
    }
}
