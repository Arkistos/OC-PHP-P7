<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ClientFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $client = new Client();
        $client->setEmail('user@client.com');
        $hashedPassword = $this->passwordHasher->hashPassword($client, 'password');
        $client->setPassword($hashedPassword);
        $this->addReference('client', $client);

        $manager->persist($client);

        $client = new Client();
        $client->setEmail('user2@client.com');
        $hashedPassword = $this->passwordHasher->hashPassword($client, 'password');
        $client->setPassword($hashedPassword);
        $this->addReference('client2', $client);

        $manager->persist($client);

        $manager->flush();
    }
}
