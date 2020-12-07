<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor\Registry;

use DSLabs\Redaktor\Registry\InMemoryRegistry;
use DSLabs\Redaktor\Registry\InvalidVersionDefinitionException;
use DSLabs\Redaktor\Version\Version;
use PhpSpec\ObjectBehavior;
use spec\DSLabs\Redaktor\Double\Revision\DummyRequestRevision;
use spec\DSLabs\Redaktor\Double\Revision\DummyResponseRevision;
use spec\DSLabs\Redaktor\Double\Revision\DummyRoutingRevision;
use Throwable;

/**
 * @see InMemoryRegistry
 */
class InMemoryRegistrySpec extends ObjectBehavior
{
    function it_retrieves_an_empty_list_when_fetching_all_revisions_if_the_registry_is_empty()
    {
        // Arrange
        $this->beConstructedWith([]);

        // Act
        $revisions = $this->retrieveAll();

        // Assert
        $revisions->shouldBeArray();
        $revisions->shouldHaveCount(0);
    }

    function it_retrieves_an_empty_list_when_fetching_the_revisions_since_the_given_version_if_the_registry_is_empty()
    {
        // Arrange
        $this->beConstructedWith([]);

        // Act
        $revisions = $this->retrieveSince(new Version('foo'));

        // Assert
        $revisions->shouldBeArray();
        $revisions->shouldHaveCount(0);
    }

    function it_retrieves_an_empty_list_when_fetching_the_revisions_since_an_inexistent_version()
    {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                DummyRoutingRevision::class,
            ],
        ]);

        // Act
        $revisions = $this->retrieveSince(new Version('bar'));

        // Assert
        $revisions->shouldBeArray();
        $revisions->shouldHaveCount(0);
    }

    function it_supports_class_name_revision_definitions()
    {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                DummyRoutingRevision::class,
                DummyRequestRevision::class,
                DummyResponseRevision::class,
            ],
        ]);

        // Assert
        $this->shouldNotThrow(Throwable::class)
            // Act
            ->duringInstantiation();
    }

    function it_supports_closure_revision_definitions()
    {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                static function () { },
            ],
        ]);

        // Assert
        $this->shouldNotThrow(Throwable::class)
            // Act
            ->duringInstantiation();
    }

    function it_supports_string_revision_definition_identifiers()
    {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                'bar',
            ],
        ]);

        // Assert
        $this->shouldNotThrow(Throwable::class)
            // Act
            ->duringInstantiation();
    }

    function it_retrieves_all_revision_definitions()
    {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                $revisionA = DummyRequestRevision::class,
                $revisionB = DummyRoutingRevision::class,
            ],
            'bar' => [
                $revisionC = DummyResponseRevision::class,
            ],
        ]);

        // Act
        $revisionsDefinitions = $this->retrieveAll();

        // Assert
        $revisionsDefinitions->shouldBeArray();
        $revisionsDefinitions->shouldHaveCount(3);
        $revisionsDefinitions->shouldMatchRevisions([
            $revisionA,
            $revisionB,
            $revisionC,
        ]);
    }

    function it_retrieves_revision_definitions_since_a_given_version()
    {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                $revisionA = DummyRequestRevision::class,
            ],
            'bar' => [
                $revisionB = DummyRequestRevision::class,
                $revisionC = DummyRequestRevision::class,
            ],
            'baz' => [
                $revisionD = DummyRequestRevision::class,
            ],
        ]);

        // Act
        $revisions = $this->retrieveSince(new Version('bar'));

        // Assert
        $revisions->shouldBeArray();
        $revisions->shouldHaveCount(3);
        $revisions->shouldMatchRevisions([
            $revisionB,
            $revisionC,
            $revisionD,
        ]);
    }

    function it_disallows_empty_version_definitions()
    {
        // Arrange
        $this->beConstructedWith([
            'foo' => [],
        ]);

        // Assert
        $this->shouldThrow(InvalidVersionDefinitionException::class)
            // Act
            ->duringInstantiation();
    }

    function it_retrieves_an_empty_list_of_available_versions()
    {
        // Act
        $versions = $this->index();

        // Assert
        $versions->shouldBe([]);
    }

    function it_retrieves_a_list_of_available_versions()
    {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                static function () {},
            ],
            'bar' => [
                static function () {},
            ],
        ]);

        // Act
        $versions = $this->index();

        // Assert
        $versions->shouldHaveCount(2);
        $versions->shouldBeArray();
        $versions->shouldIterateLike([
            new Version('foo'),
            new Version('bar'),
        ]);
    }

    public function getMatchers(): array
    {
        return [
            'matchRevisions' => static function ($versionDefinition, array $expectedRevisions): bool {
                $actualRevisions = array_map(
                    static function ($revisionDefinition) {
                        return $revisionDefinition();
                    },
                    $versionDefinition
                );

                return $actualRevisions === $expectedRevisions;
            },
        ];
    }
}
