<?php
    require_once "db.php";

    // Initialize the session
    session_start();
    
    // Check if the user is logged in, if not then redirect him to login page
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        header("location: signin.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Main page</title>
    <style>
      .wrapper {
        width: 720px;
        display: flex;
        flex-direction: column;
        margin: 0 auto;
      }

      .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 0;
      }
    </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <span>Last feteched <?php
        $db = new Db('enotio');
        echo $db->getLastTimestamp();
        $db->close();
        unset($db);
      ?></span>
      <a class="btn btn-secondary" href="logout.php">Logout</a>
    </div>
    <table class="table table-bordered table-dark">
      <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">Name</th>
          <th scope="col">Nominal</th>
          <th scope="col">Char Code</th>
          <th scope="col">Value (RUB)</th>
        </tr>
      </thead>
      <tbody>
        <?php
            $db = new Db('enotio');
            $db->getLastCurrencies();
            $db->close();
        ?>
      </tbody>
    </table>
    <table class="table table-bordered table-dark">
      <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">Name</th>
          <th scope="col">Nominal</th>
          <th scope="col">Char Code</th>
          <th scope="col">Value (RUB)</th>
        </tr>
      </thead>
      <tbody>
        <?php
            $db = new Db('enotio');
            $db->getLastCurrenciesFlipped();
            $db->close();
        ?>
      </tbody>
    </table>
  </div>
</body>
</html>
