<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $staff = new User();
        $staff->setFirstname("Administrator");
        $staff->setUsername("l4f2s4");
        $staff->setEmail("l4f2s4admin@ubucket.io");
        $staff->setTitle('superadmin');
        $staff->setPassword('$argon2id$v=19$m=65536,t=4,p=1$RElTaHJRZnBlQ243SnZvSg$znSu8P53kBNIzYdWMVYoR5KJ+AIPgFJWHJn3eKzbkHk');
        $staff->setRoles(['ROLE_ADMIN']);
        $manager->persist($staff);
        $manager->flush();
    }
}
