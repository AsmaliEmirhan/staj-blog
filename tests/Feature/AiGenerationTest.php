<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AiGeneration;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AiGenerationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * AI üretiminin kullanıcı ve yazı ilişkilerini iki yönde doğrular.
     */
    public function test_ai_generation_belongs_to_user_and_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $generation = AiGeneration::factory()
            ->forUser($user)
            ->forPost($post)
            ->create();

        $this->assertTrue($generation->user->is($user));
        $this->assertTrue($generation->post->is($post));
        $this->assertTrue($user->aiGenerations->contains($generation));
        $this->assertTrue($post->aiGenerations->contains($generation));
    }

    /**
     * AI üretiminin yazıya bağlı olmadan oluşturulabildiğini doğrular.
     */
    public function test_ai_generation_can_exist_without_post(): void
    {
        $generation = AiGeneration::factory()
            ->withoutPost()
            ->create();

        $this->assertNull($generation->post_id);
        $this->assertNull($generation->post);
    }

    /**
     * Durum scope metotlarının doğru kayıtları getirdiğini doğrular.
     */
    public function test_status_scopes_filter_ai_generations(): void
    {
        $pending = AiGeneration::factory()->create();
        $processing = AiGeneration::factory()->processing()->create();
        $completed = AiGeneration::factory()->completed()->create();
        $failed = AiGeneration::factory()->failed()->create();

        $this->assertTrue(
            AiGeneration::query()->pending()->get()->contains($pending)
        );
        $this->assertTrue(
            AiGeneration::query()->processing()->get()->contains($processing)
        );
        $this->assertTrue(
            AiGeneration::query()->completed()->get()->contains($completed)
        );
        $this->assertTrue(
            AiGeneration::query()->failed()->get()->contains($failed)
        );
    }

    /**
     * AI işleminin devam ediyor durumuna alınabildiğini doğrular.
     */
    public function test_ai_generation_can_be_marked_as_processing(): void
    {
        $generation = AiGeneration::factory()->create();

        $this->assertTrue($generation->isPending());
        $this->assertTrue($generation->markAsProcessing());

        $generation->refresh();

        $this->assertSame(
            AiGeneration::STATUS_PROCESSING,
            $generation->status
        );
        $this->assertNotNull($generation->started_at);
        $this->assertNull($generation->completed_at);
        $this->assertNull($generation->error_message);
    }

    /**
     * Başarılı işlemin sonuç ve token bilgileriyle tamamlandığını doğrular.
     */
    public function test_ai_generation_can_be_completed(): void
    {
        $generation = AiGeneration::factory()->create();

        $generation->markAsProcessing();

        $this->assertTrue($generation->complete(
            output: 'Üretilen blog içeriği.',
            promptTokens: 125,
            completionTokens: 375,
            requestId: 'request-completed-1',
            estimatedCost: '0.002500',
        ));

        $generation->refresh();

        $this->assertTrue($generation->isCompleted());
        $this->assertSame('Üretilen blog içeriği.', $generation->output);
        $this->assertSame(125, $generation->prompt_tokens);
        $this->assertSame(375, $generation->completion_tokens);
        $this->assertSame(500, $generation->total_tokens);
        $this->assertSame('request-completed-1', $generation->request_id);
        $this->assertSame('0.002500', $generation->estimated_cost);
        $this->assertNotNull($generation->duration_ms);
        $this->assertNotNull($generation->completed_at);
        $this->assertNull($generation->error_message);
    }

    /**
     * Başarısız işlemin hata bilgisiyle kapatıldığını doğrular.
     */
    public function test_ai_generation_can_fail(): void
    {
        $generation = AiGeneration::factory()->create();

        $generation->markAsProcessing();

        $this->assertTrue($generation->fail(
            errorMessage: 'Servis zaman aşımına uğradı.',
            requestId: 'request-failed-1',
        ));

        $generation->refresh();

        $this->assertTrue($generation->isFailed());
        $this->assertSame(
            'Servis zaman aşımına uğradı.',
            $generation->error_message
        );
        $this->assertSame('request-failed-1', $generation->request_id);
        $this->assertNull($generation->output);
        $this->assertNotNull($generation->duration_ms);
        $this->assertNotNull($generation->completed_at);
    }

    /**
     * AI üretim alanlarının doğru PHP türlerine dönüştürüldüğünü doğrular.
     */
    public function test_ai_generation_fields_are_cast_to_correct_types(): void
    {
        $generation = AiGeneration::factory()->completed()->create([
            'input' => ['language' => 'tr'],
            'prompt_tokens' => '25',
            'completion_tokens' => '75',
            'total_tokens' => '100',
            'estimated_cost' => '0.001250',
            'duration_ms' => '850',
        ]);

        $this->assertIsArray($generation->input);
        $this->assertSame(25, $generation->prompt_tokens);
        $this->assertSame(75, $generation->completion_tokens);
        $this->assertSame(100, $generation->total_tokens);
        $this->assertSame('0.001250', $generation->estimated_cost);
        $this->assertSame(850, $generation->duration_ms);
        $this->assertInstanceOf(
            Carbon::class,
            $generation->started_at
        );
        $this->assertInstanceOf(
            Carbon::class,
            $generation->completed_at
        );
    }

    /**
     * Sistem tarafından yönetilen alanların toplu atanamadığını doğrular.
     */
    public function test_protected_ai_generation_fields_cannot_be_mass_assigned(): void
    {
        $generation = new AiGeneration([
            'user_id' => 1,
            'post_id' => 2,
            'type' => AiGeneration::TYPE_SUMMARY,
            'provider' => 'openai',
            'model' => 'gpt-4.1-mini',
            'prompt' => 'Yazıyı özetle.',
            'input' => ['language' => 'tr'],
            'status' => AiGeneration::STATUS_COMPLETED,
            'output' => 'Yetkisiz sonuç.',
            'total_tokens' => 500,
            'estimated_cost' => '1.000000',
        ]);

        $this->assertSame(AiGeneration::TYPE_SUMMARY, $generation->type);
        $this->assertSame('openai', $generation->provider);
        $this->assertSame('gpt-4.1-mini', $generation->model);
        $this->assertSame('Yazıyı özetle.', $generation->prompt);
        $this->assertIsArray($generation->input);
        $this->assertNull($generation->user_id);
        $this->assertNull($generation->post_id);
        $this->assertNull($generation->status);
        $this->assertNull($generation->output);
        $this->assertNull($generation->total_tokens);
        $this->assertNull($generation->estimated_cost);
    }

    /**
     * Kullanıcı ve yazı silindiğinde üretim geçmişinin korunduğunu doğrular.
     */
    public function test_foreign_keys_are_set_to_null_when_related_models_are_deleted(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $generation = AiGeneration::factory()
            ->forUser($user)
            ->forPost($post)
            ->create();

        $user->delete();
        $post->forceDelete();

        $generation->refresh();

        $this->assertNull($generation->user_id);
        $this->assertNull($generation->post_id);
        $this->assertDatabaseHas('ai_generations', [
            'id' => $generation->getKey(),
        ]);
    }
}
