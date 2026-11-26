<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Persistence\Doctrine\Entity;

use App\Account\Domain\AccountId;
use App\Account\Domain\Role;
use App\Account\Infrastructure\Persistence\Doctrine\Type\AccountIdType;
use App\Account\Infrastructure\Persistence\Doctrine\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use LogicException;
use Override;
use Phil\DoctrineBehaviours\Contract\Timestampable;
use Phil\DoctrineBehaviours\TimestampableEntity;
use Phil\PrivacyBundle\Application\Contract\DataSubjectUser;
use Stringable;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use function Symfony\Component\String\u;

/**
 * An account of the site: it signs in with its user name and its hashed password. Its identifier, an AccountId stored
 * by the DBAL type AccountIdType, is also the identifier phil/privacy-bundle knows the member by, never its email
 * address. Its dates are stamped by the
 * timestamps of phil/doctrine-behaviours-bundle.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
final class User implements Stringable, UserInterface, PasswordAuthenticatedUserInterface, DataSubjectUser, Timestampable
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\Column(type: AccountIdType::NAME, unique: true)]
    private AccountId $id;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $password = '';

    /**
     * The roles granted besides ROLE_USER, which every account has.
     *
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
        private string $username,
        string $email,
    ) {
        $this->id = AccountId::generate();
        $this->email = self::normalizeEmail($email);
    }

    /**
     * An email address as the site keeps it, and as the uniqueness check of the sign-up compares it.
     */
    public static function normalizeEmail(string $email): string
    {
        return u($email)->trim()->lower()->toString();
    }

    public function getId(): AccountId
    {
        return $this->id;
    }

    #[Override]
    public function __toString(): string
    {
        return $this->username;
    }

    /**
     * The user as the session keeps it: the hashed password is hashed once more with crc32c, so the session never holds
     * it, and a changed password still signs the other sessions out.
     *
     * @return array{string, string, string, string}
     */
    public function __serialize(): array
    {
        return [$this->id->toString(), $this->username, hash('crc32c', $this->password), $this->email];
    }

    /**
     * @param array{string, string, string, string} $data
     */
    public function __unserialize(array $data): void
    {
        [$id, $this->username, $this->password, $this->email] = $data;
        $this->id = AccountId::fromString($id);
    }

    #[Override]
    public function getUserIdentifier(): string
    {
        if ('' === $this->username) {
            throw new LogicException('An account cannot have an empty user name.');
        }

        return $this->username;
    }

    #[Override]
    public function dataSubjectIdentifier(): string
    {
        return $this->id->toString();
    }

    /**
     * The roles granted to the account: ROLE_USER, then the others, each once.
     *
     * @return list<string>
     */
    #[Override]
    public function getRoles(): array
    {
        return array_values(array_unique([Role::USER, ...$this->roles]));
    }

    /**
     * @param list<string> $roles the roles besides ROLE_USER, which getRoles() adds
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    #[Override]
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}
