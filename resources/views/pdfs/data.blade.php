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
            text-align: center; /* Center the text in the header */
            background-color: #4CAF50; /* Green background color */
            color: black; /* Black text color */
        }
    </style>
</head>
<body>
<h1>Data Mahasiswa</h1>
<table>
    <thead>
    <tr>
        <th>No</th>
        <th>Nama</th>
        <th>NIM</th>
        <th>Kelas</th>
        <th>Nilai</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($records as $index => $record)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $record->student->name }}</td>
            <td>{{ $record->student->nim }}</td>
            <td>{{ $record->room->name }}</td>
            <td>{{ is_array($record->assessment) ? implode(', ', $record->assessment) : $record->assessment }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>