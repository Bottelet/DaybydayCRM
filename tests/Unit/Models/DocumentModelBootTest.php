<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\Document;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DocumentModelBootTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $user;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->client = Client::factory()->create(['user_id' => $this->user->id]);
    }

    #[Test]
    public function document_stores_explicit_external_id_when_provided()
    {
        $externalId = Uuid::uuid4()->toString();

        $document = Document::create([
            'external_id' => $externalId,
            'size' => 1.5,
            'path' => '/path/to/file.pdf',
            'original_filename' => 'file.pdf',
            'mime' => 'application/pdf',
            'integration_type' => 'local',
            'source_type' => Client::class,
            'source_id' => $this->client->id,
        ]);

        $this->assertNotNull($document->external_id);
        $this->assertNotEmpty($document->external_id);
        $this->assertEquals($externalId, $document->external_id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $document->external_id
        );
    }

    #[Test]
    public function document_preserves_provided_external_id()
    {
        $customExternalId = 'custom-document-uuid-abcd';

        $document = Document::create([
            'external_id' => $customExternalId,
            'size' => 1.5,
            'path' => '/path/to/file.pdf',
            'original_filename' => 'file.pdf',
            'mime' => 'application/pdf',
            'integration_type' => 'local',
            'source_type' => Client::class,
            'source_id' => $this->client->id,
        ]);

        $this->assertEquals($customExternalId, $document->external_id);
    }

    #[Test]
    public function document_generates_unique_external_ids_for_each_record()
    {
        $document1 = Document::create([
            'external_id' => Uuid::uuid4()->toString(),
            'size' => 1.0,
            'path' => '/path/to/file1.pdf',
            'original_filename' => 'file1.pdf',
            'mime' => 'application/pdf',
            'integration_type' => 'local',
            'source_type' => Client::class,
            'source_id' => $this->client->id,
        ]);

        $document2 = Document::create([
            'external_id' => Uuid::uuid4()->toString(),
            'size' => 2.0,
            'path' => '/path/to/file2.pdf',
            'original_filename' => 'file2.pdf',
            'mime' => 'application/pdf',
            'integration_type' => 'local',
            'source_type' => Client::class,
            'source_id' => $this->client->id,
        ]);

        $this->assertNotEquals($document1->external_id, $document2->external_id);
    }

    #[Test]
    public function document_has_sourceable_morph_to_relationship()
    {
        $document = Document::factory()->create([
            'source_type' => Client::class,
            'source_id' => $this->client->id,
        ]);

        $relationship = $document->source();

        $this->assertInstanceOf(MorphTo::class, $relationship);
        $this->assertTrue(method_exists($document, 'source'));
    }

    #[Test]
    public function document_factory_creates_record_with_external_id()
    {
        $task = Task::factory()->create();
        $document = Document::factory()->create([
            'source_type' => Task::class,
            'source_id' => $task->id,
        ]);

        $this->assertNotNull($document->external_id);
        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'external_id' => $document->external_id,
        ]);
    }
}
