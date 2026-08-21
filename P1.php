<!DOCTYPE html>
<html>
<head>
  <title>Jobe Portal</title>
  <!--<?php include 'links.php'; ?>
   <?php include 'CSS/style.css'; ?> -->
</head>
<body>

<div class="container register">
      <div class="row">
          <div class="col-md-3 register-left">
              <img src="https://image.ibb.co/n7oTvU/logo_white.png" alt=""/>
              <h3>Welcome</h3>
              <p>Please fill all the details carefully. This form can change your life. </p>
              <a href="home.php">Check Your Form</a><br/>
          </div>

    <div>
        <h1>Apply for Web Developer Post</h1>

        <form method="POST" action="">

            <div>
                <input type="text" class="form-control" placeholder="Enter Your Name *" name="name" value="" required/>

                <input type="text" class="form-control" placeholder="Enter Your Qualification *" name="qualification" value="" required/>

            </div>

            <br>

            <div>
                <input type="tel" class="form-control" placeholder="Mobile Number *" name="mobile" value="" required/>

                <input type="email" class="form-control" placeholder="Email *" name="email" value="" required/>
            </div>

            <br>

            <div>
                <input type="text" class="form-control" placeholder="Any References *" name="refer" value="" required/>

                <input type="text" name="job"value="Web Developer" readonly>
            </div>

            <br>

            <input type="submit" name="register" value="Register">

        </form>
    </div>
</div>

<?php

if (isset($_POST['register'])) {

    $name = $_POST['name'];
    $qualification = $_POST['qualification'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    $reference = $_POST['reference'];

    echo "<h2>Registration Successful</h2>";

    echo "Name: " . $name . "<br>";
    echo "Qualification: " . $qualification . "<br>";
    echo "Mobile: " . $mobile . "<br>";
    echo "Email: " . $email . "<br>";
    echo "Reference: " . $reference . "<br>";
    echo "Job: Web Developer";
}

?>

</body>
</html>

<?php
include 'connection.php';
?>