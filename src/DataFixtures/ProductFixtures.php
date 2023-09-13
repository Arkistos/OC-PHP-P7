<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createProduct(
            'Iphone 14',
            'Apple',
            1445.34,
            '14ème génération d\'iphone',
        ));

        $manager->persist($this->createProduct(
            'Galaxy 14',
            'Samsung',
            1423,
            '14ème génération de smartphone Samsung',
        ));

        $manager->flush();
    }

    private function createProduct(string $name, string $brand, float $price, string $description): Product
    {
        $product = new Product();
        $product->setName($name);
        $product->setBrand($brand);
        $product->setPrice($price);
        $product->setDescription($description);
        $product->setCreatedAt(new \DateTimeImmutable());

        return $product;
    }
}
