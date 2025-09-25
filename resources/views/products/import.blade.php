<!DOCTYPE html>
<html>

<head>
    <title>CSV Import</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h2>Import Products CSV</h2>

        @if (session('summary'))
            <div class="alert alert-success">
                <strong>Import Summary:</strong><br>
                Total: {{ session('summary.total') }}<br>
                Created: {{ session('summary.created') }}<br>
                Updated: {{ session('summary.updated') }}<br>
                Invalid: {{ session('summary.invalid') }}<br>
                Duplicates: {{ session('summary.duplicates') }}
            </div>
        @endif

        <form action="{{ route('products.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="csv_file" class="form-label">Select CSV file</label>
                <input type="file" name="csv_file" id="csv_file" class="form-control" required>
                @error('csv_file')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Import</button>
        </form>
    </div>
</body>

</html>
