<?php

namespace App\Factory;

use App\Entity\User;
use App\Enum\CivilityEnum;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<User>
 */
final class UserFactory extends ModelFactory
{
    public const DEFAULT_PASSWORD = 'password';

    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
    }

    protected function getDefaults(): array
    {
        $firstName = self::faker()->firstName();
        $lastName = self::faker()->lastName();

        return [
            'civility' => self::faker()->randomElement(CivilityEnum::cases()),
            'email' => sprintf('%s.%s@%s', strtolower($firstName), strtolower($lastName), 'yopmail.com'),
            'firstName' => $firstName,
            'lastName' => $lastName,
            'isVerified' => self::faker()->boolean(),
            'password' => self::DEFAULT_PASSWORD,
            'referentCompanies' => [],
            'companyMemberships' => [],
            'roles' => [],
        ];
    }

    protected function initialize(): self
    {
        return $this
            ->withoutPersisting()
            ->afterInstantiate(function (User $user, array $attributes) {
                if (null !== $attributes['password']) {
                    $user->setPassword($this->passwordHasher->hashPassword($user, $attributes['password']));
                }
            })
        ;
    }

    protected static function getClass(): string
    {
        return User::class;
    }
}
