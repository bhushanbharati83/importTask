<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;  // ← ye add karo
use App\Models\Upload;

class UploadController extends Controller
{
    public function initiate(Request $req)
    {
        $id = (string) Str::uuid();
        Upload::create([
            'id' => $id,
            'filename' => $req->filename,
            'size' => $req->size,
        ]);
        return ['upload_id' => $id];
    }

    public function chunk(Request $req, $id)
    {
        $chunk = $req->file('chunk');
        $path = storage_path("app/tmp/{$id}");
        if (!is_dir($path)) mkdir($path, 0777, true);

        $chunk->move($path, $req->input('index') . ".part");
        return ['status' => 'ok'];
    }

    public function complete(Request $req, $id)
    {
        $path = storage_path("app/tmp/{$id}");
        $final = storage_path("app/uploads/{$id}_final.jpg");

        $out = fopen($final, 'ab');
        foreach (glob("$path/*.part") as $file) {
            $in = fopen($file, 'rb');
            stream_copy_to_stream($in, $out);
            fclose($in);
        }
        fclose($out);

        $sha = hash_file('sha256', $final);
        if ($req->checksum && $sha !== $req->checksum) {
            return response()->json(['error' => 'Checksum mismatch'], 400);
        }

        $img = Image::make($final);

        // Resize variants
        $variants = [];
        foreach ([256, 512, 1024] as $size) {
            $resized = storage_path("app/uploads/{$id}_{$size}.jpg");
            $img->resize($size, null, function ($c) {
                $c->aspectRatio();
            });
            $img->save($resized);
            $variants[$size] = $resized;
        }

        ImageModel::create([
            'upload_id' => $id,
            'path' => $final,
            'variants' => json_encode($variants),
        ]);

        return ['status' => 'completed'];
    }
}
