<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/libs/bootstrap.min.css">
    <script type="text/javascript" src="/libs/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/dataTables.bootstrap5.min.css"/>
	<script type="text/javascript" src="/js/jquery-3.5.1.js"></script>
	<script type="text/javascript" src="/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="/js/dataTables.bootstrap5.min.js"></script>
	<script type="text/javascript" src="/js/apicontroller.js"></script>
    <title><?php echo $title; ?></title>
</head>

<body>
    <?php
    echo view('templates/modal');
    echo view('templates/toast');
    echo view('templates/menu');
    echo view('index', $data);
    echo view('templates/footer');
    ?>
</body>

</html>