<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_routes_require_authentication(): void
    {
        $this->getJson('/api/books')->assertUnauthorized();
        $this->postJson('/api/books', [])->assertUnauthorized();
    }

    public function test_books_can_be_paginated_and_searched(): void
    {
        $user = User::factory()->create();
        Book::factory()->create(['title' => 'Laskar Pelangi', 'author' => 'Andrea Hirata']);
        Book::factory()->create(['title' => 'Sang Pemimpi', 'author' => 'Andrea Hirata']);
        Book::factory()->create(['title' => 'Bumi Manusia', 'author' => 'Pramoedya Ananta Toer']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/books?search=Andrea&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.author', 'Andrea Hirata')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.per_page', 1);
    }

    public function test_book_can_be_created_and_duplicate_isbn_is_rejected(): void
    {
        $user = User::factory()->create();
        $payload = [
            'title' => 'Laskar Pelangi',
            'author' => 'Andrea Hirata',
            'publisher' => 'Bentang Pustaka',
            'isbn' => '9789793062792',
            'published_year' => 2005,
            'stock' => 10,
            'description' => 'Novel tentang anak-anak Belitung.',
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/books', $payload)
            ->assertCreated()
            ->assertJsonPath('data.title', 'Laskar Pelangi');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/books', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('isbn');
    }

    public function test_book_can_be_shown_and_partially_updated(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'Judul Lama']);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/books/{$book->id}")
            ->assertOk()
            ->assertJsonPath('data.title', 'Judul Lama');

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/books/{$book->id}", ['title' => 'Judul Baru'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Judul Baru');

        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Judul Baru']);
    }

    public function test_book_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/books/{$book->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Buku berhasil dihapus');

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}
