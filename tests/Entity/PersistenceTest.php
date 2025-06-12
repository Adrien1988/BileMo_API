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


    /** Build & drop the schema once for the whole test class */
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
        /* -------------------- Client -------------------- */
        $client = (new Client())
            ->setName('ACME')
            ->setWebsite('https://acme.example')
            ->setContactEmail('contact@acme.example')
            ->setContactPhone('+33123456789')
            ->setAddress("1 rue de la Paix\n75002 Paris")
            // contractStart is nullable, we leave it null on purpose
        ;

        /* -------------------- Product ------------------- */
        $product = (new Product())
            ->setName('iPhone 15')
            ->setDescription('Apple flagship')
            ->setPrice('1199.00')
            ->setBrand('Apple')
            ->setImageUrl('https://example.com/iphone15.png')
            ->setColor('Black')
            ->setStorageCapacity(256)
            ->setRam(8)
            ->setScreenSize('6.1')
            ->setCameraResolution('48 MP')
            ->setOperatingSystem('iOS 17')
            ->setBatteryCapacity('5000 mAh')
        ;

        /* --------------------- User --------------------- */
        $user = (new User())
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setEmail('john@acme.com')
            ->setPassword('hashedpass')
            ->setRole(UserRole::ROLE_ADMIN)
            ->setClient($client)
        ;

        /* ------------------ Persist/flush ---------------- */
        self::$em->persist($client);
        self::$em->persist($product);
        self::$em->persist($user);
        self::$em->flush();

        /* ------------------- Clear & reload -------------- */
        $ids = [
            'client'  => $client->getId(),
            'product' => $product->getId(),
            'user'    => $user->getId(),
        ];
        self::$em->clear();

        $reloadedClient = self::$em->find(Client::class, $ids['client']);
        $reloadedProduct = self::$em->find(Product::class, $ids['product']);
        $reloadedUser = self::$em->find(User::class, $ids['user']);

        /* -------------------- Assertions ---------------- */
        self::assertSame('ACME', $reloadedClient->getName());
        self::assertSame('https://acme.example', $reloadedClient->getWebsite());

        self::assertSame('iPhone 15', $reloadedProduct->getName());
        self::assertSame('Black', $reloadedProduct->getColor());

        self::assertSame('john@acme.com', $reloadedUser->getEmail());
        self::assertSame($reloadedClient, $reloadedUser->getClient());

    }


}
