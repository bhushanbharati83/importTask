<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Upload;
use App\Models\Image as ImageModel;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

class ProductController extends Controller
{
    public function showForm()
    {
        return view('products.import');
    }
    public function importCsv_old(Request $req)
    {
        $file = $req->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        $header = fgetcsv($handle);
        $summary = ['total' => 0, 'created' => 0, 'updated' => 0, 'invalid' => 0, 'duplicates' => 0];

        $seen = [];
        while (($row = fgetcsv($handle)) !== false) {
            $summary['total']++;
            $data = array_combine($header, $row);
            //     dd($data);
            if (empty($data['sku']) || empty($data['name'])) {
                $summary['invalid']++;
                continue;
            }

            if (in_array($data['sku'], $seen)) {
                $summary['duplicates']++;
                continue;
            }
            $seen[] = $data['sku'];

            $product = Product::updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'name' => $data['name'],
                    'price' => (float) $data['price'] ?? null
                ]
            );

            $product->wasRecentlyCreated ? $summary['created']++ : $summary['updated']++;
        }
        fclose($handle);

        return $summary;
    }

    public function importCsv(Request $req)
    {

        $req->validate([
            'csv_file' => 'required|mimes:csv,txt|max:51200', 
        ]);

        $handle = fopen($req->file('csv_file')->getRealPath(), 'r');
        $header = fgetcsv($handle);

        $batchSize = 500;
        $rows = [];
        $summary = ['total' => 0, 'created' => 0, 'updated' => 0, 'invalid' => 0, 'duplicates' => 0];
        $seen = [];
        //  dd('before loop');
        while (($row = fgetcsv($handle)) !== false) {
            // dd('in loop');
            $summary['total']++;
            $data = array_combine($header, $row);

            // invalid row
            if (empty($data['sku']) || empty($data['name'])) {
                $summary['invalid']++;
                continue;
            }

            if (in_array($data['sku'], $seen)) {
                $summary['duplicates']++;
                continue;
            }
            $seen[] = $data['sku'];

            $rows[] = $data;

            //dd($rows);
            // process batch
            if (count($rows) >= $batchSize) {
                $this->processRows($rows, $summary);
                $rows = [];
            }
        }

        // remaining rows
        if (!empty($rows)) {
            $this->processRows($rows, $summary);
        }

        fclose($handle);
        // return $summary;
        return view('products.summary', compact('summary'));
    }

    protected function processRows(array $rows, array &$summary)
    {
        foreach ($rows as $data) {
            $product = Product::updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'name' => $data['name'],
                    'price' => (float) ($data['price'] ?? 0),
                ]
            );

            $product->wasRecentlyCreated ? $summary['created']++ : $summary['updated']++;

            // handle image column
            if (!empty($data['image'])) {

                $uploadId = $this->processImage($data['image']);
                if ($uploadId) {
                    $product->primary_upload_id = $uploadId;
                    $product->save();
                }
            }
        }
    }
    protected function processImage(string $filename, string $folder = 'csv_images')
    {
        $localPath = storage_path("app/{$folder}/{$filename}");

        if (!file_exists($localPath)) {
            return null; // file not found
        }

        // check if Upload already exists
        $upload = Upload::where('filename', $filename)->first();
        if ($upload && $upload->status === 'completed') {
            return $upload->id; // already processed
        }

        // create new upload
        $id = (string) Str::uuid();
        $upload = Upload::create([
            'id' => $id,
            'filename' => $filename,
            'size' => filesize($localPath),
            'status' => 'completed', // mark completed for local processing
        ]);

        // generate variants
        $variants = [];
        $sizes = [256, 512, 1024];
        foreach ($sizes as $s) {
            $variantPath = storage_path("app/uploads/{$id}_{$s}.jpg");

            $img = Image::make($localPath);
            $img->resize($s, null, function ($c) {
                $c->aspectRatio();
                $c->upsize();
            });
            $img->save($variantPath, 85); // quality 85
            $variants[$s] = $variantPath;
        }

        // save ImageModel record
        ImageModel::create([
            'upload_id' => $id,
            'path' => $localPath,
            'variants' => json_encode($variants),
        ]);

        return $id;
    }
}
