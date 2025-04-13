<?php
include("conn.php");
$id = $_SESSION['id'];


if (isset($_POST['save_btn'])) {
  $name = $_POST['name'];
  $sql = "INSERT INTO cities(name)VALUES('$name')";
  if ($query = mysqli_query($db, $sql)) {
    echo '<script>alert("city inserted successfully")</script>';
  } else {
    echo '<script>alert("insert failed check your inputs")</script>';
  }
}
?>


<!DOCTYPE html>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<style>
  body {
    font-family: Arial, Helvetica, sans-serif;
  }

  .navbar {
    width: 100%;
    background-color: #174f96;
    overflow: auto;
  }

  .navbar a {
    float: right;
    padding: 10px;
    color: white;
    text-decoration: none;
    font-size: 17px;
  }

  

  .row {
    margin-left: 0em;
    margin-right: 0em;
  }
</style>

<body style="background-image: url('images/bg.jpeg');background-size: cover;background-repeat:no-repeat;height:100vh" class="gray-bg">

  <?php
  include('header.php');
  ?>
  <form action="add_city.php" method="post" enctype="multipart/form-data">
    <div class="container-fluid justify-content-center w-75 mt-5 p-4" style="border-color: #fff;border-style: solid;border-radius: 35px;background:#fff">
      <div class="row justify-content-center mt-2">
        <div class="col-6" style="text-align: center;">
          <h3 style="color: #a04c4b;font-weight: bold;"> Add City </h3>
        </div>
      </div>

      <div class="row justify-content-center mt-2">
        <div class="col-6 mt-1" style="text-align: center;">
          <h3 style="background-color: #a04c4b;color: #fff;"> name </h3>
        </div>
        <div class="col-6" style="text-align: center;">
          <input name="name" class="p-2 w-100" type="text"  required> </input>
        </div>
      </div>

      <div class="row justify-content-center mt-2">
        <div class="col-6" style="text-align: center;">
          <button name="save_btn" type="submit" class="btn btn-danger w-50" style="background-color: #a04c4b;color: #fff;"> save</button>
        </div>
      </div>
    </div>
  </form>

  </div>

</body>

</html>