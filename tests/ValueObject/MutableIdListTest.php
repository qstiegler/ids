<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\ValueObject;

use DigitalCraftsman\Ids\Test\ValueObject\MutableUserIdList;
use DigitalCraftsman\Ids\Test\ValueObject\ProjectId;
use DigitalCraftsman\Ids\Test\ValueObject\UserId;
use DigitalCraftsman\Ids\Test\ValueObject\UserIdList;
use DigitalCraftsman\Ids\ValueObject\Exception\DuplicateIds;
use DigitalCraftsman\Ids\ValueObject\Exception\IdAlreadyInList;
use DigitalCraftsman\Ids\ValueObject\Exception\IdClassNotHandledInList;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListDoesContainId;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListDoesNotContainId;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListIsNotEmpty;
use DigitalCraftsman\Ids\ValueObject\Exception\IdListsMustBeEqual;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \DigitalCraftsman\Ids\ValueObject\MutableIdList */
final class MutableIdListTest extends TestCase
{
    // -- Construct

    /**
     * @test
     * @covers ::__construct
     * @doesNotPerformAssertions
     */
    public function id_list_construction_works(): void
    {
        // -- Arrange & Act
        new MutableUserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::mustNotContainDuplicateIds
     */
    public function id_list_construction_fails_with_duplicates(): void
    {
        // -- Assert
        $this->expectException(DuplicateIds::class);

        // -- Arrange & Act
        $duplicateId = UserId::generateRandom();

        new MutableUserIdList([
            $duplicateId,
            $duplicateId,
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::mustOnlyContainIdsOfHandledClass
     */
    public function id_list_construction_fails_with_ids_of_different_id_class(): void
    {
        // -- Assert
        $this->expectException(IdClassNotHandledInList::class);

        // -- Arrange & Act
        new MutableUserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
            ProjectId::generateRandom(),
        ]);
    }

    /**
     * @test
     * @covers ::fromIds
     * @doesNotPerformAssertions
     */
    public function id_list_construction_from_ids_works(): void
    {
        // -- Arrange & Act
        MutableUserIdList::fromIds([
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);
    }

    /**
     * @test
     * @covers ::emptyList
     */
    public function empty_list_works(): void
    {
        // -- Arrange
        $emptyIdList = MutableUserIdList::emptyList();

        // -- Act & Assert
        self::assertCount(0, $emptyIdList);
    }

    // -- Merge

    /**
     * @test
     * @covers ::fromIdLists
     */
    public function from_id_lists_works(): void
    {
        // -- Arrange
        $idList1 = new MutableUserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        $idList2 = new MutableUserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        // -- Act
        $mergedIdList = UserIdList::fromIdLists([
            $idList1,
            $idList2,
        ]);

        // -- Assert
        self::assertCount(6, $mergedIdList);
    }

    /**
     * @test
     * @covers ::fromIdLists
     */
    public function from_id_lists_with_duplicates_works(): void
    {
        // -- Arrange
        $idList1 = new MutableUserIdList([
            new UserId('41918847-b781-4046-94ce-2fddf5674d9e'),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        $idList2 = new MutableUserIdList([
            new UserId('41918847-b781-4046-94ce-2fddf5674d9e'),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        // -- Act
        $mergedIdList = MutableUserIdList::fromIdLists([
            $idList1,
            $idList2,
        ]);

        // -- Assert
        self::assertCount(5, $mergedIdList);
    }

    // -- Add id

    /**
     * @test
     * @covers ::addId
     */
    public function add_id_works(): void
    {
        // -- Arrange
        $idList = new MutableUserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        $newId = UserId::generateRandom();

        // -- Act
        $idList->addId($newId);

        // -- Assert
        self::assertCount(3, $idList);

        self::assertTrue($idList->containsId($newId));
    }

    /**
     * @test
     * @covers ::addId
     */
    public function add_id_fails_with_duplicate_id(): void
    {
        // -- Assert
        $this->expectException(IdAlreadyInList::class);

        // -- Arrange
        $existingUserId = UserId::generateRandom();
        $idList = new MutableUserIdList([
            $existingUserId,
            UserId::generateRandom(),
        ]);

        // -- Act
        $idList->addId($existingUserId);
    }

    /**
     * @test
     * @covers ::addIdWhenNotInList
     */
    public function add_id_when_not_in_list_works(): void
    {
        // -- Arrange
        $existingId = UserId::generateRandom();
        $idList = new MutableUserIdList([
            $existingId,
            UserId::generateRandom(),
        ]);

        $newId = UserId::generateRandom();

        // -- Act
        $idList->addIdWhenNotInList($existingId);
        $idList->addIdWhenNotInList($newId);

        // -- Assert
        self::assertCount(3, $idList);

        self::assertTrue($idList->containsId($existingId));
        self::assertTrue($idList->containsId($newId));
    }

    // -- Remove id

    /**
     * @test
     * @covers ::removeId
     */
    public function remove_id_works(): void
    {
        // -- Arrange
        $idToRemove = UserId::generateRandom();

        $idList = new MutableUserIdList([
            $idToRemove,
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        // -- Act
        $idList->removeId($idToRemove);

        // -- Assert
        self::assertCount(2, $idList);

        self::assertTrue($idList->notContainsId($idToRemove));
    }

    // -- Diff

    /**
     * @test
     * @covers ::diff
     */
    public function id_list_diff_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $fullList1 = MutableUserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);
        $partialList1 = MutableUserIdList::fromIds([
            $idAnton,
            $idPaul,
        ]);

        $fullList2 = MutableUserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);
        $partialList2 = MutableUserIdList::fromIds([
            $idAnton,
            $idPaul,
        ]);

        // -- Act
        $fullList1->diff($partialList1);
        $partialList2->diff($fullList2);

        // -- Assert
        self::assertCount(2, $fullList1);
        self::assertCount(2, $partialList2);

        self::assertTrue($fullList1->containsId($idMarkus));
        self::assertTrue($fullList1->containsId($idTom));

        self::assertTrue($partialList2->containsId($idMarkus));
        self::assertTrue($partialList2->containsId($idTom));
    }

    /**
     * @test
     * @covers ::diff
     */
    public function id_list_diff_works_with_empty(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $fullList1 = MutableUserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);
        $emptyList1 = MutableUserIdList::emptyList();

        $fullList2 = MutableUserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);
        $emptyList2 = MutableUserIdList::emptyList();

        // -- Act
        $fullList1->diff($emptyList1);
        $emptyList2->diff($fullList2);

        // -- Assert
        self::assertCount(4, $fullList1);
        self::assertCount(4, $emptyList2);
    }

    // -- Intersect

    /**
     * @test
     * @covers ::intersect
     */
    public function id_list_intersect_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $fullList = MutableUserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $partialList = MutableUserIdList::fromIds([
            $idAnton,
            $idPaul,
        ]);

        // -- Act
        $fullList->intersect($partialList);

        // -- Assert
        self::assertCount(2, $fullList);

        self::assertTrue($fullList->containsId($idAnton));
        self::assertTrue($fullList->containsId($idPaul));
    }

    // -- Must and must not contain

    /**
     * @test
     * @covers ::mustContainId
     * @covers ::mustNotContainId
     * @doesNotPerformAssertions
     */
    public function id_list_must_and_must_not_contains_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $listWithAllIds = MutableUserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $partialList = MutableUserIdList::fromIds([
            $idAnton,
            $idPaul,
        ]);

        // -- Act & Assert
        $listWithAllIds->mustContainId($idMarkus);
        $partialList->mustNotContainId($idMarkus);
    }

    /**
     * @test
     * @covers ::mustContainId
     */
    public function id_list_must_contain_throws_exception(): void
    {
        // -- Assert
        $this->expectException(IdListDoesNotContainId::class);

        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();

        $partialList = MutableUserIdList::fromIds([
            $idAnton,
            $idPaul,
        ]);

        // -- Act
        $partialList->mustContainId($idMarkus);
    }

    /**
     * @test
     * @covers ::mustNotContainId
     */
    public function id_list_must_not_contain_throws_exception(): void
    {
        // -- Assert
        $this->expectException(IdListDoesContainId::class);

        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idPaul = UserId::generateRandom();

        $partialList = MutableUserIdList::fromIds([
            $idAnton,
            $idPaul,
        ]);

        // -- Act
        $partialList->mustNotContainId($idAnton);
    }

    // -- Must be empty

    /**
     * @test
     * @covers ::mustBeEmpty
     * @doesNotPerformAssertions
     */
    public function id_list_must_be_empty_works(): void
    {
        // -- Arrange
        $emptyList = MutableUserIdList::emptyList();

        // -- Act
        $emptyList->mustBeEmpty();
    }

    /**
     * @test
     * @covers ::mustBeEmpty
     */
    public function id_list_must_be_empty_throws_exception_when_not_empty(): void
    {
        // -- Assert
        $this->expectException(IdListIsNotEmpty::class);

        // -- Arrange
        $notEmptyList = new MutableUserIdList([
            UserId::generateRandom(),
        ]);

        // -- Act
        $notEmptyList->mustBeEmpty();
    }

    // -- Empty

    /**
     * @test
     * @covers ::isEmpty
     * @covers ::isNotEmpty
     */
    public function id_list_is_empty_works(): void
    {
        // -- Arrange
        $emptyList = UserIdList::emptyList();
        $notEmptyList = new MutableUserIdList([
            UserId::generateRandom(),
        ]);

        // -- Act & Assert
        self::assertTrue($emptyList->isEmpty());
        self::assertFalse($notEmptyList->isEmpty());

        self::assertTrue($notEmptyList->isNotEmpty());
        self::assertFalse($emptyList->isNotEmpty());
    }

    // -- Map

    /**
     * @test
     * @covers ::map
     */
    public function id_list_map_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $listWithAllIds = MutableUserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $expectedArray = [
            (string) $idAnton,
            (string) $idMarkus,
            (string) $idPaul,
            (string) $idTom,
        ];

        // -- Act
        $stringArray = $listWithAllIds->map(
            static fn (UserId $userId) => (string) $userId,
        );

        // -- Assert
        self::assertSame($expectedArray, $stringArray);
    }

    // -- Contains

    /**
     * @test
     * @covers ::containsId
     * @covers ::notContainsId
     */
    public function id_list_contains_and_not_contains_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $listWithAllIds = MutableUserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $partialList = MutableUserIdList::fromIds([
            $idAnton,
            $idPaul,
        ]);

        // -- Act & Assert
        self::assertTrue($listWithAllIds->containsId($idAnton));
        self::assertFalse($partialList->containsId($idMarkus));

        self::assertTrue($partialList->notContainsId($idMarkus));
        self::assertFalse($listWithAllIds->notContainsId($idMarkus));
    }

    // -- Is equal and not equal

    /**
     * @test
     * @covers ::isEqualTo
     * @covers ::isNotEqualTo
     */
    public function id_list_is_equal_to(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $idMarc = UserId::generateRandom();

        $originalList = MutableUserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $copyOfOriginalList = MutableUserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $partialList = MutableUserIdList::fromIds([
            $idAnton,
            $idPaul,
        ]);

        $listWithOneExchanged = MutableUserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idMarc,
        ]);

        // -- Act & Assert
        self::assertTrue($originalList->isEqualTo($copyOfOriginalList));
        self::assertFalse($originalList->isEqualTo($partialList));
        self::assertFalse($originalList->isEqualTo($listWithOneExchanged));

        self::assertTrue($originalList->isNotEqualTo($partialList));
        self::assertFalse($originalList->isNotEqualTo($copyOfOriginalList));
    }

    /**
     * @test
     * @covers ::isEqualTo
     * @covers ::isNotEqualTo
     */
    public function empty_id_list_is_not_equal_to(): void
    {
        // -- Arrange
        $idTom = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();

        $mutableEmptyUserIdList = MutableUserIdList::fromIds([]);
        $mutableUserIdList = MutableUserIdList::fromIds([
            $idTom,
            $idMarkus,
        ]);

        // -- Act & Assert
        $this->assertFalse($mutableEmptyUserIdList->isEqualTo($mutableUserIdList));

        $this->assertTrue($mutableEmptyUserIdList->isNotEqualTo($mutableUserIdList));
    }

    /**
     * @test
     * @covers ::isEqualTo
     * @covers ::isNotEqualTo
     */
    public function id_list_is_not_equal_to_empty_id_list(): void
    {
        // -- Arrange
        $idTom = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();

        $mutableEmptyUserIdList = MutableUserIdList::fromIds([]);
        $mutableUserIdList = MutableUserIdList::fromIds([
            $idTom,
            $idMarkus,
        ]);

        // -- Act & Assert
        $this->assertFalse($mutableUserIdList->isEqualTo($mutableEmptyUserIdList));

        $this->assertTrue($mutableUserIdList->isNotEqualTo($mutableEmptyUserIdList));
    }

    /**
     * @test
     * @covers ::mustBeEqualTo
     */
    public function must_not_be_equal_to(): void
    {
        // -- Assert
        $this->expectException(IdListsMustBeEqual::class);

        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $originalList = MutableUserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $partialList = MutableUserIdList::fromIds([
            $idAnton,
            $idPaul,
        ]);

        // -- Act
        $originalList->mustBeEqualTo($partialList);
    }

    // -- Count

    /**
     * @test
     * @covers ::count
     */
    public function id_list_count_works(): void
    {
        // -- Arrange
        $idList = new MutableUserIdList([
            UserId::generateRandom(),
            UserId::generateRandom(),
            UserId::generateRandom(),
        ]);

        // -- Act & Assert
        self::assertSame(3, $idList->count());
    }

    // -- Iteration

    /**
     * @test
     * @covers ::current
     * @covers ::next
     * @covers ::key
     * @covers ::rewind
     * @covers ::valid
     */
    public function id_list_iteration_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();

        $idList = new MutableUserIdList([
            $idAnton,
            $idMarkus,
            $idPaul,
        ]);

        $expectedString = sprintf(
            '%d%s%d%s%d%s',
            0,
            (string) $idAnton,
            1,
            (string) $idMarkus,
            2,
            (string) $idPaul,
        );

        // -- Act
        $concatenatedIds = '';
        foreach ($idList as $key => $id) {
            $concatenatedIds .= (string) $key;
            $concatenatedIds .= (string) $id;
        }

        // -- Assert
        self::assertSame($expectedString, $concatenatedIds);
    }

    /**
     * @test
     * @covers ::current
     * @covers ::next
     * @covers ::key
     * @covers ::rewind
     * @covers ::valid
     */
    public function id_list_works_with_gaps_in_input_list(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();

        $idList = new MutableUserIdList([
            0 => $idAnton,
            1 => $idMarkus,
            3 => $idPaul,
        ]);

        $expectedString = sprintf(
            '%s%s%s',
            (string) $idAnton,
            (string) $idMarkus,
            (string) $idPaul,
        );

        // -- Act
        $concatenatedIds = '';
        foreach ($idList as $id) {
            $concatenatedIds .= (string) $id;
        }

        // -- Assert
        self::assertSame($expectedString, $concatenatedIds);
    }

    /**
     * @test
     * @covers ::isInSameOrder
     * @covers ::idAtPosition
     * @covers ::intersect
     */
    public function id_list_is_in_same_order_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        // Ordered alphabetically
        $orderedIdList = MutableUserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        // In order but with missing ids
        $idListThatIsInOrder = MutableUserIdList::fromIds([
            $idAnton,
            $idPaul,
        ]);

        $idListThatIsNotInOrder = MutableUserIdList::fromIds([
            $idPaul,
            $idMarkus,
        ]);

        // -- Act & Assert
        self::assertTrue($idListThatIsInOrder->isInSameOrder($orderedIdList));
        self::assertFalse($idListThatIsNotInOrder->isInSameOrder($orderedIdList));
    }

    /**
     * @test
     * @covers ::idsAsStringList
     */
    public function id_list_as_string_works(): void
    {
        // -- Arrange
        $idAnton = UserId::generateRandom();
        $idMarkus = UserId::generateRandom();
        $idPaul = UserId::generateRandom();
        $idTom = UserId::generateRandom();

        $orderedIdList = MutableUserIdList::fromIds([
            $idAnton,
            $idMarkus,
            $idPaul,
            $idTom,
        ]);

        $expectedArray = [
            (string) $idAnton,
            (string) $idMarkus,
            (string) $idPaul,
            (string) $idTom,
        ];

        // -- Act & Assert
        self::assertSame($expectedArray, $orderedIdList->idsAsStringList());
    }
}
