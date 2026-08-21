<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apply for Web Developer Post</title>

<!-- Bootstrap CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
<!-- Custom CSS -->
<link rel="stylesheet" href="CSS/style.css">
</head>
<body>

<div class="page-wrap">
  <div class="card-outer">
    <div class="row g-0 h-100">

      <!-- Left panel -->
      <div class="col-lg-4 left-panel d-flex flex-column justify-content-center align-items-center text-center">
        <h1 class="welcome-title">Welcome</h1>
        <p class="welcome-text">
          Please fill all the details carefully. This form can change your life.
        </p>
        <button type="button" class="btn btn-check-form">Check Form</button>
      </div>

      <!-- Right panel -->
      <div class="col-lg-8 right-panel d-flex align-items-center">
        <div class="form-container w-100">
          <h2 class="form-title text-center">Apply for Web Developer Post</h2>

          <form>
            <div class="row">
              <div class="col-md-6 mb-3">
                <input type="text" class="form-control custom-input" placeholder="enter your name *" required>
              </div>
              <div class="col-md-6 mb-3">
                <input type="text" class="form-control custom-input" placeholder="enter your qualification *" required>
              </div>

              <div class="col-md-6 mb-3">
                <input type="tel" class="form-control custom-input" placeholder="mobile number *" required>
              </div>
              <div class="col-md-6 mb-3">
                <input type="email" class="form-control custom-input" placeholder="email id *" required>
              </div>

              <div class="col-md-6 mb-3">
                <input type="text" class="form-control custom-input" placeholder="Any references *" required>
              </div>
              <div class="col-md-6 mb-3">
                <input type="text" class="form-control custom-input" value="Web Developer" readonly>
              </div>
            </div>

            <div class="text-end mt-2">
              <button type="submit" class="btn btn-register">Register</button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</div>

</body>
</html>