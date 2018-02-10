<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\User;
use App\Gallery;

class FileUploadTest extends TestCase
{
    /**
     * Test upload, view and delete file.
     *
     * @return void
     */
    public function testUploadFile()
    {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/gallery', [
            'image' => UploadedFile::fake()->image('image.jpg')
        ]);
        $response->assertStatus(200)->assertJsonStructure(['id', 'src']);

        $gallery = Gallery::find($response->original['id']);

        Storage::assertExists($gallery->file_path);

        $this->actingAs($user)->get('/gallery/' . $response->original['src'])
        ->assertStatus(200);
        $this->actingAs($user)->get('/gallery/worg_src')
        ->assertStatus(404);

        $this->actingAs($user2)->delete('/gallery/' . $response->original['id'])
        ->assertStatus(403);
        $this->actingAs($user)->delete('/gallery/' . $response->original['id'])
        ->assertStatus(200);

        Storage::assertMissing($gallery->file_path);
    }
}
