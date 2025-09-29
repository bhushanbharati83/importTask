<!DOCTYPE html>
<html>

<head>
    <title>CSV Import Summary</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        h2 {
            color: #333;
        }

        table {
            border-collapse: collapse;
            width: 400px;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ccc;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f5f5f5;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            color: #fff;
            font-size: 0.9em;
        }

        .primary {
            background-color: #007bff;
        }

        .success {
            background-color: #28a745;
        }

        .info {
            background-color: #17a2b8;
        }

        .danger {
            background-color: #dc3545;
        }

        .warning {
            background-color: #ffc107;
            color: #000;
        }
    </style>
</head>

<body>
    <h2>CSV Import Summary</h2>

    <table>
        <tr>
            <th>Total Rows</th>
            <td><span class="badge primary">{{ $summary['total'] }}</span></td>
        </tr>
        <tr>
            <th>Created</th>
            <td><span class="badge success">{{ $summary['created'] }}</span></td>
        </tr>
        <tr>
            <th>Updated</th>
            <td><span class="badge info">{{ $summary['updated'] }}</span></td>
        </tr>
        <tr>
            <th>Invalid Rows</th>
            <td><span class="badge danger">{{ $summary['invalid'] }}</span></td>
        </tr>
        <tr>
            <th>Duplicates</th>
            <td><span class="badge warning">{{ $summary['duplicates'] }}</span></td>
        </tr>
    </table>
</body>

</html>
