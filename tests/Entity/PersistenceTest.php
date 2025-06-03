<?php

namespace App\Tests\Entity;

use App\Entity\Client;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\UserRole;
use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PersistenceTest extends KernelTestCase
{
    private static EntityManagerInterface $em;


    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }


    /** crée/détruit le schéma une seule fois pour toute la classe */
    public static function setUpBeforeClass(): void
    {
        self::bootKernel();
        self::$em = self::getContainer()->get('doctrine')->getManager();

        $tool = new SchemaTool(self::$em);
        $metadata = self::$em->getMetadataFactory()->getAllMetadata();

        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);
    }


    public static function tearDownAfterClass(): void
    {
        self::$em->getConnection()->close();
        self::ensureKernelShutdown();
    }


    public function testPersistAndReload(): void
    {
        $client = (new Client())->setName('ACME');
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

        self::$em->persist($client);
        self::$em->persist($product);
        self::$em->persist($user);
        self::$em->flush();

        $ids = [
            'client'  => $client->getId(),
            'product' => $product->getId(),
            'user'    => $user->getId(),
        ];
        self::$em->clear();

        $reloadedClient = self::$em->find(Client::class, $ids['client']);
        $reloadedProduct = self::$em->find(Product::class, $ids['product']);
        $reloadedUser = self::$em->find(User::class, $ids['user']);

        self::assertSame('ACME', $reloadedClient->getName());
        self::assertSame('iPhone 15', $reloadedProduct->getName());
        self::assertSame('john@acme.com', $reloadedUser->getEmail());
        self::assertSame($reloadedClient, $reloadedUser->getClient());
    }
}
