<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\Upload;
use App\Models\ImageModel;
use App\Http\Controllers\ProductController;

class CsvImportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_imports_csv_and_processes_images_correctly()
    {
        // Step 1: Fake storage for images
        Storage::fake('local');

        // Step 2: Create fake CSV file
        $csvContent = "sku,name,price,image\n";
        $csvContent .= "SKU001,Product One,100,image1.jpg\n";
        $csvContent .= "SKU002,Product Two,200,image2.jpg\n";

        $csvFile = UploadedFile::fake()->createWithContent('test.csv', $csvContent);

        // Step 3: Create fake image files in storage
        Storage::put('csv_images/image1.jpg', 'fake image content');
        Storage::put('csv_images/image2.jpg', 'fake image content');

        // Step 4: Call importCsv
        $controller = new ProductController();
        $response = $controller->importCsv(new \Illuminate\Http\Request([
            'csv_file' => $csvFile
        ]));

        // Step 5: Assert products inserted
        $this->assertDatabaseHas('products', ['sku' => 'SKU001', 'name' => 'Product One']);
        $this->assertDatabaseHas('products', ['sku' => 'SKU002', 'name' => 'Product Two']);

        // Step 6: Assert uploads created
        $this->assertDatabaseHas('uploads', ['filename' => 'image1.jpg']);
        $this->assertDatabaseHas('uploads', ['filename' => 'image2.jpg']);

        // Step 7: Assert image variants created
        $this->assertDatabaseCount('image_models', 2);

        // Step 8: Assert summary counts
        $summary = $response->getData()['summary'] ?? null;

        $this->assertNotNull($summary);
        $this->assertEquals(2, $summary['total']);
        $this->assertEquals(2, $summary['created']);
        $this->assertEquals(0, $summary['updated']);
        $this->assertEquals(0, $summary['invalid']);
        $this->assertEquals(0, $summary['duplicates']);
    }
}
