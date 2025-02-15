<!DOCTYPE html>
<html>
<head>
    <title>Data Mahasiswa</title>
    <meta charset="UTF-8">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<h1>Data Mahasiswa</h1>
<table>
    <thead>
    <tr>
        <th>Nama</th>
        <th>Kelas</th>
        <th>Nilai</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($records as $record)
        <tr>
            <td>{{ $record->student->name }}</td>
            <td>{{ $record->room->name }}</td>
            <td>{{ $record->score_as_string }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>