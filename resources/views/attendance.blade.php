<!DOCTYPE html>
<html>

<head>
    <style>
    #customers {
        font-family: Arial, Helvetica, sans-serif;
        border-collapse: collapse;
        width: 100%;
    }

    #customers td,
    #customers th {
        border: 1px solid #ddd;
        padding: 8px;
    }

    #customers tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    #customers tr:hover {
        background-color: #ddd;
    }

    #customers th {
        padding-top: 12px;
        padding-bottom: 12px;
        text-align: left;
        background-color: #04AA6D;
        color: white;
    }

    .btn {
        display: inline-block;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        color: #ffffff;
        font-weight: bold;
    }

    .custom-button {
        background-color: #ff0000;
    }

    .custom-button:hover {
        background-color: #990000;
    }
    </style>
</head>

<body>

    <a href="{{ url('/check-connection') }}" class="btn custom-button">Process</a>


    <table id="customers">
        <tr>
            <th>Device ID</th>
            <th>Employee ID</th>
            <th>Check Time</th>
        </tr>
        <?php
        foreach ($attendances as $data):?>
        <tr>
            <td><?= $data->device_id ?></td>
            <td><?= $data->employee_id ?></td>
            <td><?= $data->checktime ?></td>
        </tr>

        <?php endforeach
        ?>
    </table>

</body>

</html>