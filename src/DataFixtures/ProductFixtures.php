<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;
use Faker;

;

class ProductFixtures extends Fixture
{
    public const product_count = 50;

    public function __construct(private SluggerInterface $slugger){}

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        for($i = 0; $i < self::product_count; $i++){
            $product = new Product;
            $product
                ->setName($faker->text(15))
                ->setDescription($faker->text())
                ->setSlug($this->slugger->slug($product->getName())->lower())
                ->setPrice($faker->numberBetween(500, 70000)) //Price displayed in cents.
                ->setStock($faker->numberBetween(0, 2000))
            ;
            $category = $this->getReference(
                'cat-'.CategoryFixtures::$non_parent[
                    rand(0, count(CategoryFixtures::$non_parent) - 1)
                ]
            );
            $product->setCategorie($category);
            $this->setReference('prod-'.$i, $product);
            $manager->persist($product);
        };
        $manager->flush();
    }
}