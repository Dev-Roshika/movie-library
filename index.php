<?php
function sanitizeInput($data)
{
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function validateEmail($email)
{
  return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateMobile($mobile)
{
  return preg_match('/^\d{10}$/', $mobile);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $errors = [];

  $firstname = isset($_POST['firstname']) ? sanitizeInput($_POST['firstname']) : '';
  if (empty($firstname)) {
    $errors['firstname'] = 'First name is required';
  }

  $lastname = isset($_POST['lastname']) ? sanitizeInput($_POST['lastname']) : '';
  if (empty($lastname)) {
    $errors['lastname'] = 'Last name is required';
  }

  $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
  if (empty($email)) {
    $errors['email'] = 'Email is required';
  } elseif (!validateEmail($email)) {
    $errors['email'] = 'Invalid email format';
  }

  $mobile = isset($_POST['mobile']) ? sanitizeInput($_POST['mobile']) : '';
  if (!empty($mobile) && !validateMobile($mobile)) {
    $errors['mobile'] = 'Invalid mobile number';
  }

  $message = isset($_POST['message']) ? sanitizeInput($_POST['message']) : '';

  if (empty($errors)) {
    $data = array(
      'firstname' => $firstname,
      'lastname' => $lastname,
      'email' => $email,
      'mobile' => $mobile,
      'message' => $message
    );

    $jsonFilePath = 'data.json';

    if (file_exists($jsonFilePath)) {
      $existingData = json_decode(file_get_contents($jsonFilePath), true);

      $existingData[] = $data;
    } else {
      $existingData = array($data);
    }

    $jsonData = json_encode($existingData, JSON_PRETTY_PRINT);

    file_put_contents($jsonFilePath, $jsonData);
  }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Movie Selection Site</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
  <link rel="stylesheet" href="styles.css" />
  <script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>

<body>
  <div class="topnav" id="myTopnav">
    <a href="#" class="logo">
      <img src="img/Logo.png" alt="logo" />
    </a>
    <a href="#home" class="active"> HOME</a>
    <a href="#ourscreens">OUR SCREENS </a>
    <a href="#schedule">SCHEDULE</a>
    <a href="#movielibrary">MOVIE LIBRARY </a>
    <a href="#locationcontact">LOCATION & CONTACT </a>
    <a href="javascript:void(0);" class="icon" onclick="myFunction()">
      <i class="fa fa-bars"></i>
    </a>
  </div>
  <div class="slideshow-container">
    <div class="mySlides">
      <div class="numbertext">1 / 4</div>
      <img src="img/banner1.jpeg" style="width: 100%" />
    </div>

    <div class="mySlides">
      <div class="numbertext">2 / 4</div>
      <img src="img/banner2.jpg" style="width: 100%" />
    </div>

    <div class="mySlides">
      <div class="numbertext">3 / 4</div>
      <img src="img/banner3.jpg" style="width: 100%" />
    </div>

    <div class="mySlides">
      <div class="numbertext">4 / 4</div>
      <img src="img/banner4.jpg" style="width: 100%" />
    </div>
  </div>

  <div style="text-align: center; background-color: #0f0f0f">
    <span class="dot"></span>
    <span class="dot"></span>
    <span class="dot"></span>
    <span class="dot"></span>
  </div>

  <div class="site-introduction">
    <div class="movie-library">
      <h1>MOVIE LIBRARY</h1>
      <p>
        Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam
        nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
        sed diam voluptua.
      </p>
    </div>
  </div>
  <div id="app">
    <div class="collect-fav">
      <div class="collect-fav content">
        <div class="fav-title">
          <h3>Collect your favourites</h3>
        </div>
        <div class="fav-search" style="position: relative">
          <i class="fa fa-search" style="color: white; position: absolute; left: 10px; top: 8px"></i>
          <input type="text" v-model="searchQuery" @input="searchMovies" style="width: 30vw; height: 20px" placeholder="          Search title and add to grid" />
        </div>
      </div>
      <div>
        <hr style="margin-top: 5px; border-color: white" />
      </div>
    </div>

    <div class="grid" v-if="searchQuery">
      <div v-for="movie in filteredMovies" :key="movie.id" class="movie-card">
        <img :src="movie.image.medium" :alt="movie.name" />
        <div class="movie-card-details">
          <h3>{{ movie.name }}</h3>
          <div v-html="removePTags(movie.summary)"></div>
        </div>
        <button @click="addToSelection(movie)">Add to Favourites</button>
      </div>
    </div>

    <!-- Selected movies grid -->
    <div class="grid">
      <div v-for="selectedMovie in selectedMovies" :key="selectedMovie.id" class="movie-card" style="position: relative">
        <button class="btn-close" @click="removeFromSelection(selectedMovie)">
          &times;
        </button>
        <img :src="selectedMovie.image.medium" :alt="selectedMovie.name" />
        <div class="movie-card-details">
          <h3>{{ selectedMovie.name }}</h3>
          <div v-html="removePTags(selectedMovie.summary)" class="movie-summary"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="contact-us-container">
    <div class="contact-us">
      <div>
        <h3>How to reach us</h3>
        <p>Lorem ipsum dolor sit amet, consetetur.</p>
      </div>
      <div class="container">
        <form action="" method="post" onsubmit="return validateForm()">
          <div class="row">
            <div>
              <label for="fname">First Name *</label>
              <input type="text" id="fname" name="firstname" required />
              <span id="fnameError" class="error"></span>
            </div>
            <div>
              <label for="lname">Last Name *</label>
              <input type="text" id="lname" name="lastname" required />
              <span id="lnameError" class="error"></span>

            </div>
          </div>

          <label for="email">Email *</label>
          <input type="email" id="email" name="email" required />
          <span id="emailError" class="error"></span>

          <label for="mobile">Telephone</label>
          <input type="tel" id="mobile" name="mobile" />
          <span id="mobileError" class="error"></span>

          <label for="message">Message</label>
          <textarea id="message" name="message" style="height: 200px"></textarea>

          <input type="submit" value="Submit" />
        </form>
      </div>
    </div>
    <div class="google-map">
      <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3638.376725264747!2d-122.08376078492411!3d37.4222489798884!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x808580f9febbc1e3%3A0x30c4146b457c1b9e!2sGoogleplex!5e0!3m2!1sen!2sus!4v1629797298411!5m2!1sen!2sus" width="600" height="450" style="border: 0;" allowfullscreen="" loading="lazy"></iframe>
    </div>
  </div>
  <div class="contact-us-container-gap"></div>

  <section class="footer">
    <div class="footer-row">
      <div class="footer-col">
        <h4 style="font-size: 16px; font-weight: bold">IT Group</h4>
        <div class="add">
          <p>C. Salvador de Madariaga, 1</p>
          <p>28027 Madrid</p>
          <p>Spain</p>
        </div>
      </div>
      <div class="footer-col">
        <div class="icons">
          <p style="color: white">Follow us on</p>
          <i class="fa-brands fa-twitter"></i>
          <i class="fa-brands fa-youtube"></i>
        </div>
      </div>
    </div>

    <div class="footer-row" style="display: flex; justify-content: space-around">
      <div class="footer-col">
        <p style="color: #b7b7b7; font-size: 14px">Copyright © 2022 IT Hote ls. All rights reserved.</p>
      </div>
      <div class="footer-col" style="display: flex; align-items: center;">
        <p style="color: #b7b7b7; font-size: 14px">
          Photos by Felix Mooneeram &
          <a href="https://unsplash.com/@serge_k">Serge Kutuzov</a>
          on
          <a href="https://unsplash.com/">Unsplash</a>
        </p>
      </div>
    </div>
  </section>

  <script>
    new Vue({
      el: "#app",
      data: {
        movies: [],
        selectedMovies: [],
        searchQuery: "",
      },
      mounted() {
        // Fetch data from API
        axios
          .get("https://api.tvmaze.com/shows/1/episodes")
          .then((response) => {
            this.movies = response.data;
            console.log(this.movies);
          })
          .catch((error) => {
            console.error("Error fetching data:", error);
          });
      },
      computed: {
        // Filter movies based on search query
        filteredMovies() {
          return this.movies.filter((movie) =>
            movie.name.toLowerCase().includes(this.searchQuery.toLowerCase())
          );
        },
      },
      methods: {
        addToSelection(movie) {
          this.selectedMovies.push(movie);
          this.searchQuery = "";
        },
        removeFromSelection(movie) {
          this.selectedMovies = this.selectedMovies.filter(
            (selectedMovie) => selectedMovie.id !== movie.id
          );
        },
        // Search movies based on input query
        searchMovies() {
          // Make an API call for searching movies 
        },
        removePTags(summary) {
          return summary.replace(/<p>/g, "").replace(/<\/p>/g, "");
        },
      },
    });
  </script>
  <script>
    function myFunction() {
      var x = document.getElementById("myTopnav");
      if (x.className === "topnav") {
        x.className += " responsive";
      } else {
        x.className = "topnav";
      }
    }

    let slideIndex = 0;
    showSlides();

    function showSlides() {
      let i;
      let slides = document.getElementsByClassName("mySlides");
      let dots = document.getElementsByClassName("dot");
      for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
      }
      slideIndex++;
      if (slideIndex > slides.length) {
        slideIndex = 1;
      }
      for (i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
      }
      slides[slideIndex - 1].style.display = "block";
      dots[slideIndex - 1].className += " active";
      setTimeout(showSlides, 4000);
    }

    function validateForm() {
      var isValid = true;
      var fname = document.getElementById("fname").value;
      var lname = document.getElementById("lname").value;
      var email = document.getElementById("email").value;
      var mobile = document.getElementById("mobile").value;

      if (fname === "") {
        document.getElementById("fnameError").innerText = "First name is required";
        isValid = false;
      } else {
        document.getElementById("fnameError").innerText = "";
      }

      if (lname === "") {
        document.getElementById("lnameError").innerText = "Last name is required";
        isValid = false;
      } else {
        document.getElementById("lnameError").innerText = "";
      }

      if (email === "") {
        document.getElementById("emailError").innerText = "Email is required";
        isValid = false;
      } else {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
          document.getElementById("emailError").innerText = "Invalid email format";
          isValid = false;
        } else {
          document.getElementById("emailError").innerText = "";
        }
      }

      if (mobile !== "") {
        var mobileRegex = /^\d{10}$/;
        if (!mobileRegex.test(mobile)) {
          document.getElementById("mobileError").innerText = "Invalid mobile number";
          isValid = false;
        } else {
          document.getElementById("mobileError").innerText = "";
        }
      }

      return isValid;
    }
  </script>
</body>

</html>