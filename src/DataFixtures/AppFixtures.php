<?php
// A partir du moment, où ça devient fonctionnel, on met tt en commentaire, et on laisse juste "user" car le reste sera compléter par le "user" et donc on en a plus besoin...

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Invoice;
use App\Entity\Customer;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }


    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // admin 
        $adminUser = new User();
        $adminUser->setFirstName('Jordan')
            ->setLastName('Berti')
            ->setEmail('berti@epse.be')
            ->setPassword($this->passwordHasher->hashPassword($adminUser, 'password'))
            ->setRoles(['ROLE_ADMIN']);
        $manager->persist($adminUser);

        // gestion des users
        // chrono, c'est comme l'identifiant de la facture, c'est ce qui permet d'identifier à qui appartient la facture...
        for ($u = 0; $u < 10; $u++) {
            $chrono = 1;
            $user = new User();
            $user->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setEmail($faker->email())
                ->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $manager->persist($user);
            // gestion des clients 
            for ($c = 0; $c < rand(5, 20); $c++) {
                $customer = new Customer();
                $customer->setFirstName($faker->firstName())
                    ->setLastName($faker->lastName())
                    ->setCompany($faker->company())
                    ->setEmail($faker->email())
                    ->setUser($user);
                $manager->persist($customer);

                // gestion des factures
                // On créé entre 3 à 10 factures
                for ($i = 0; $i < rand(3, 10); $i++) {
                    $invoice = new Invoice();
                    $invoice->setAmount($faker->randomfloat(2, 250, 5000))

                        // Entre la date d'aujourd'hui et -6 mois, ici, on est dans des fixtures, donc tt est superficiel, c'est juste pour créer ma base de données mais de manière aléatoire...
                        ->setSentAt($faker->dateTimeBetween('-6 months'))
                        ->setStatus($faker->randomElement(['SENT', 'PAID', 'CANCELLED']))
                        ->setCustomer($customer)
                        ->setChrono($chrono);
                    $chrono++;
                    $manager->persist($invoice);
                }
            }
        }

        $manager->flush();
    }
}
