<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject;

use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use DigitalCraftsman\Ids\ValueObject\Exception\IdEqual;
use DigitalCraftsman\Ids\ValueObject\Exception\IdNotEqual;
use DigitalCraftsman\Ids\ValueObject\Exception\InvalidId;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/** @coversDefaultClass \DigitalCraftsman\Ids\ValueObject\Id */
final class IdTest extends TestCase
{
    /**
     * @test
     * @covers ::__construct
     * @covers ::fromString
     * @doesNotPerformAssertions
     */
    public function construction_works(): void
    {
        // -- Act
        new UserId('f41e0af4-88c4-4d79-9c1a-6e8ea34a956f');
        UserId::fromString('f41e0af4-88c4-4d79-9c1a-6e8ea34a956f');
    }

    /**
     * @test
     * @covers ::__construct
     */
    public function construction_with_invalid_id_fails(): void
    {
        // -- Assert
        $this->expectException(InvalidId::class);

        // -- Act
        new UserId('test');
    }

    /**
     * @test
     * @covers ::isEqualTo
     */
    public function user_id_is_equal(): void
    {
        // -- Arrange
        $userId1 = UserId::fromString('f41e0af4-88c4-4d79-9c1a-6e8ea34a956f');
        $userId2 = UserId::fromString('f41e0af4-88c4-4d79-9c1a-6e8ea34a956f');

        // -- Act & Assert
        self::assertTrue($userId1->isEqualTo($userId2));
    }

    /**
     * @test
     * @covers ::isNotEqualTo
     */
    public function user_id_is_not_equal(): void
    {
        // -- Arrange
        $userId1 = UserId::generateRandom();
        $userId2 = UserId::generateRandom();

        // -- Act & Assert
        self::assertTrue($userId1->isNotEqualTo($userId2));
    }

    /**
     * @test
     * @covers ::isExistingInList
     * @covers ::isNotExistingInList
     */
    public function user_id_is_existing_in_list(): void
    {
        // -- Arrange
        $uuid = Uuid::uuid4()->toString();

        $userIdToSearch = new UserId($uuid);

        $userId1 = UserId::generateRandom();
        $userId2 = UserId::generateRandom();

        $userIdNotInList = UserId::generateRandom();

        $listOfUserIdsIncludingSameInstance = [
            $userIdToSearch,
            $userId1,
            $userId2,
        ];

        $copyOfStringValue = UserId::fromString((string) $userIdToSearch);

        $listOfUserIdsIncludingEqualInstance = [
            $copyOfStringValue,
            $userId1,
            $userId2,
        ];

        // -- Act & Assert
        self::assertTrue($userIdToSearch->isExistingInList($listOfUserIdsIncludingSameInstance));
        self::assertTrue($userIdToSearch->isExistingInList($listOfUserIdsIncludingEqualInstance));
        self::assertTrue($userIdNotInList->isNotExistingInList($listOfUserIdsIncludingEqualInstance));
    }

    /**
     * @test
     * @covers ::isExistingInList
     */
    public function user_id_is_not_existing_in_list(): void
    {
        // -- Arrange
        $uuid = Uuid::uuid4()->toString();

        $userIdToSearch = new UserId($uuid);

        $userId1 = UserId::generateRandom();
        $userId2 = UserId::generateRandom();

        $listOfUserIdsWithoutIdToSearch = [
            $userId1,
            $userId2,
        ];

        // -- Act & Assert
        self::assertFalse($userIdToSearch->isExistingInList($listOfUserIdsWithoutIdToSearch));
    }

    /**
     * @test
     * @covers ::mustBeEqualTo
     * @doesNotPerformAssertions
     */
    public function user_id_must_be_equal(): void
    {
        // -- Arrange
        $userId1 = UserId::generateRandom();
        $userId2 = UserId::fromString((string) $userId1);

        // -- Act
        $userId1->mustBeEqualTo($userId2);
    }

    /**
     * @test
     * @covers ::mustNotBeEqualTo
     * @doesNotPerformAssertions
     */
    public function user_id_must_not_be_equal(): void
    {
        // -- Arrange
        $userId1 = UserId::generateRandom();
        $userId2 = UserId::generateRandom();

        // -- Act
        $userId1->mustNotBeEqualTo($userId2);
    }

    /**
     * @test
     * @covers ::mustBeEqualTo
     */
    public function user_id_must_be_equal_fails(): void
    {
        // -- Assert
        $this->expectException(IdNotEqual::class);

        // -- Arrange
        $userId1 = UserId::generateRandom();
        $userId2 = UserId::generateRandom();

        // -- Act
        $userId1->mustBeEqualTo($userId2);
    }

    /**
     * @test
     * @covers ::mustNotBeEqualTo
     */
    public function user_id_must_not_be_equal_fails(): void
    {
        // -- Assert
        $this->expectException(IdEqual::class);

        // -- Arrange
        $userId1 = UserId::generateRandom();
        $userId2 = new UserId((string) $userId1);

        // -- Act
        $userId1->mustNotBeEqualTo($userId2);
    }
}
