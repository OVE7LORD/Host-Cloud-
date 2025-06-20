<header class="">
      <nav class="navbar navbar-expand-lg">
        <div class="container">
          <a class="navbar-brand" href="index.php"><h2>Host <em>Cloud</em></h2></a>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ml-auto">
              <li class="nav-item active">
                <a class="nav-link" href="index.php">Home
                  <span class="sr-only">(current)</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="about.php">About Us</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="services.php">Our Services</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="contact.php">Contact Us</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="QnA.php">Q&A</a>
              </li>
            </ul>
          </div>
          <div class="functional-buttons">
            <ul>
              <?php
              if(isset($_SESSION['user_id'])) {
                  echo '<li><a href="logout.php">Logout</a></li>';
                  echo '<li><a href="#">Welcome, ' . htmlspecialchars($_SESSION['username']) . '</a></li>';
              } else {
                  echo '<li><a href="login.php">Log in</a></li>';
                  echo '<li><a href="register.php">Sign Up</a></li>';
              }
              ?>
            </ul>
          </div>
        </div>
      </nav>
    </header>