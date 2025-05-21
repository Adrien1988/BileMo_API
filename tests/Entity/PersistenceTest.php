<?php

namespace App\Tests\Entity;

use App\Entity\Client;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\UserRole;
use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PersistenceTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public function testPersistAndReload(): void
    {
        self::bootKernel();
        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get('doctrine')->getManager();

        $client  = (new Client())->setName('ACME');
        $product = (new Product())
            ->setName('iPhone 15')
            ->setDescription('Apple flagship')
            ->setPrice('1199.00')
            ->setBrand('Apple')
            ->setImageUrl('https://example.com/iphone15.png');

        $user = (new User())
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setEmail('john@acme.com')
            ->setPassword('hashedpass')
            ->setRole(UserRole::ROLE_ADMIN)
            ->setClient($client);

        $em->persist($client);
        $em->persist($product);
        $em->persist($user);
        $em->flush();

        $ids = [
                'client'  => $client->getId(),
                'product' => $product->getId(),
                'user'    => $user->getId(),
               ];
        $em->clear();

        $reloadedClient  = $em->find(Client::class, $ids['client']);
        $reloadedProduct = $em->find(Product::class, $ids['product']);
        $reloadedUser    = $em->find(User::class, $ids['user']);

        self::assertSame('ACME', $reloadedClient->getName());
        self::assertSame('iPhone 15', $reloadedProduct->getName());
        self::assertSame('john@acme.com', $reloadedUser->getEmail());
        self::assertSame($reloadedClient, $reloadedUser->getClient());
    }
}
