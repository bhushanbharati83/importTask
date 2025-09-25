<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Models\User;

class ProcessCsvChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function handle()
    {
        foreach ($this->rows as $data) {
            try {
                $imageUrl = $data['image_url'];
                $imageContents = file_get_contents($imageUrl);

                $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $filename = uniqid() . "." . $extension;

                // save original
                Storage::disk('public')->put("uploads/original/" . $filename, $imageContents);

                // create resized versions (using Intervention/Image)
                $image = Image::make($imageContents);

                // thumbnail 150x150
                $thumb = clone $image;
                $thumb->fit(150, 150);
                Storage::disk('public')->put("uploads/thumbs/" . $filename, (string) $thumb->encode());

                // medium 500x500
                $medium = clone $image;
                $medium->fit(500, 500);
                Storage::disk('public')->put("uploads/medium/" . $filename, (string) $medium->encode());

                // save to DB
                User::create([
                    'name'  => $data['name'],
                    'email' => $data['email'],
                    'image' => "uploads/original/" . $filename,
                    'image_thumb' => "uploads/thumbs/" . $filename,
                    'image_medium' => "uploads/medium/" . $filename,
                ]);
            } catch (\Exception $e) {
                \Log::error("CSV Row failed: " . $e->getMessage());
            }
        }
    }
}
