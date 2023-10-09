<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

;

class CategoryFixtures extends Fixture
{
    private $count = 1;
    public static $non_parent = [];

    public function __construct(private SluggerInterface $slugger){}

    public function createCategoryFixtures(string $name, Category $parent = null, ObjectManager $manager)
    {
        $category = new Category();
        $category
            ->setName($name)
            ->setSlug($this->slugger->slug($category->getName())->lower())
            ->setParent($parent)
            ->setCategoryOrder($this->count)
        ;
        $manager->persist($category);
        $this->addReference('cat-'.$this->count, $category);
        if($parent != null){
            self::$non_parent[] = $this->count;
        };
        $this->count++;
        return $category;
    }

    public function load(ObjectManager $manager): void
    {
        # First fake category
        $parent = $this->createCategoryFixtures('Boulangerie', null, $manager);
        $this->createCategoryFixtures('Pâtisserie', $parent, $manager);
        $this->createCategoryFixtures('Viennoiseries', $parent, $manager);
        
        # Second fake category
        $parent2 = $this->createCategoryFixtures('Informatique', null, $manager);
        $this->createCategoryFixtures('Écran', $parent2, $manager);
        $this->createCategoryFixtures('Ordinateur', $parent2, $manager);
        $this->createCategoryFixtures('Souris', $parent2, $manager);

        # Third fake category
        $parent3 = $this->createCategoryFixtures('Vêtements', null, $manager);
        $this->createCategoryFixtures('Maillot', $parent3, $manager);
        $this->createCategoryFixtures('Pantalon', $parent3, $manager);
        $this->createCategoryFixtures('Veste', $parent3, $manager);

        # Flush all fake categories
        $manager->flush();
    }
}
