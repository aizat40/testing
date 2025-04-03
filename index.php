<?php
include 'config.php';
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}
$email = $_SESSION['email']; // Ambil email pengguna yang login

// Query untuk mendapatkan jumlah status berdasarkan email pengguna
$query = "SELECT status, COUNT(*) as count FROM maintainance_orders WHERE email = ? GROUP BY status";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$data = [
    "Pending" => 0,
    "In Progress" => 0,
    "Closed" => 0
];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[$row['status']] = (int)$row['count'];
    }
}

$conn->close(); // Tutup connection
?> 
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Maintenance Order</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="../assets/img/logoamari.png" rel="icon">
  <link href="../assets/img/logoamari.png" rel="apple-touch-icon">
  <!-- Fonts -->                                        
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="../assets/vendor/animate.css/animate.min.css" rel="stylesheet">
  <link href="../assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="../assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="status_chart.js"></script>
  <!-- Main CSS File -->
  <link href="../assets/css/main.css" rel="stylesheet">

  <style>
    .icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 120px; /* Adjust width */
  height: 120px; /* Adjust height */
}

.dashboard-box {
        background-color: #ffffff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease-in-out;
    }

    .dashboard-box:hover {
        box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2);
        transform: translateY(-10px);
    }
  </style>

</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

    <a href="index.php" class="logo d-flex align-items-center" style="gap: 15px;">
            <img src="../assets/img/logoamari.png" style="width:100px; height: 100px;" alt="Amari Logo">
            <h2 class="sitename m-0" style="font-size: 1.8rem; white-space: nowrap;">Amari Johor Bahru</h2>
        </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php" class="active">Home</a></li>
          
          <li class="dropdown"><a href="#"><span><?php echo($_SESSION['name'])?></span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>

              <li><a href="#" onclick="confirmLogout()">Logout</a></li>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmLogout() {
    Swal.fire({
        title: 'Are you sure?',
        text: "You will be logged out!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, Logout!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'logout.php'; // Redirect only if confirmed
        }
    });
}
let chart;

function fetchData() {
    const year = document.getElementById("year").value;
    const month = document.getElementById("month").value;

    console.log(`Fetching data for Year: ${year}, Month: ${month}`);

    fetch(`data.php?year=${year}&month=${month}&t=${new Date().getTime()}`)
        .then(response => response.json())
        .then(data => {
            const labels = data.map(item => item.status);
            const values = data.map(item => Math.round(item.total)); // Round values to whole numbers

            // Tetapkan warna berdasarkan status
            const colorMap = {
                "Closed": "rgba(255, 99, 132, 0.8)",  // Merah
                "Pending": "rgba(255, 206, 86, 0.8)", // Kuning
                "In Progress": "rgba(54, 162, 235, 0.8)" // Biru
            };

            const backgroundColors = labels.map(label => colorMap[label] || "rgba(75, 192, 192, 0.8)"); // Default warna

            if (chart) chart.destroy();

            const ctx = document.getElementById("statusChart").getContext("2d");
            chart = new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Total Issues",
                        data: values,
                        backgroundColor: backgroundColors,
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            labels: {
                                generateLabels: function(chart) {
                                    const labels = chart.data.labels;
                                    return labels.map((label, i) => ({
                                        text: `${label} (${values[i]})`,
                                        fillStyle: backgroundColors[i],
                                        strokeStyle: backgroundColors[i],
                                        lineWidth: 2
                                    }));
                                }
                            }
                        }
                    }
                }
            });
        });
}

document.addEventListener("DOMContentLoaded", function() {
    fetchData(); // Panggil sekali masa page load

    document.getElementById("year").addEventListener("change", fetchData);
    document.getElementById("month").addEventListener("change", fetchData);
});


</script>

            </ul>
          </li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section dark-background">

      <div id="hero-carousel" data-bs-interval="5000" class="container carousel carousel-fade" data-bs-ride="carousel">

        <!-- Slide 1 -->
        <!-- Slide 1 -->
<div class="carousel-item active">
  <div class="carousel-container">
    <h2 class="animate__animated animate__fadeInDown">Efficient Maintenance Management</h2>
    <p class="animate__animated animate__fadeInUp">Track and manage maintenance requests seamlessly. Ensure timely service and smooth operations with our user-friendly system.</p>
  </div>
</div>

<!-- Slide 2 -->
<div class="carousel-item">
  <div class="carousel-container">
    <h2 class="animate__animated animate__fadeInDown">Stay Updated on Maintenance Progress</h2>
    <p class="animate__animated animate__fadeInUp">Receive real-time updates on your maintenance requests. Monitor status changes and ensure all issues are resolved efficiently.</p>
    
  </div>
</div>

<!-- Slide 3 -->
<div class="carousel-item">
  <div class="carousel-container">
    <h2 class="animate__animated animate__fadeInDown">Streamlined Maintenance Workflow</h2>
    <p class="animate__animated animate__fadeInUp">Submit, track, and manage maintenance reports with ease. Our system helps reduce downtime and improve response times.</p>
  
  </div>
</div>

        <a class="carousel-control-prev" href="#hero-carousel" role="button" data-bs-slide="prev">
          <span class="carousel-control-prev-icon bi bi-chevron-left" aria-hidden="true"></span>
        </a>

        <a class="carousel-control-next" href="#hero-carousel" role="button" data-bs-slide="next">
          <span class="carousel-control-next-icon bi bi-chevron-right" aria-hidden="true"></span>
        </a>

      </div>

      <svg class="hero-waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28 " preserveAspectRatio="none">
        <defs>
          <path id="wave-path" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z"></path>
        </defs>
        <g class="wave1">
          <use xlink:href="#wave-path" x="50" y="3"></use>
        </g>
        <g class="wave2">
          <use xlink:href="#wave-path" x="50" y="0"></use>
        </g>
        <g class="wave3">
          <use xlink:href="#wave-path" x="50" y="9"></use>
        </g>
      </svg>
</div>
    </section><!-- /Hero Section -->

    <!-- Services Section -->
    <section id="services" class="services section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Services</h2>
        <p>What we do offer</p>
      </div><!-- End Section Title -->

      <div class="container">
        <div class="row gy-4 justify-content-center"> <!-- Added justify-content-center -->
        
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
            <div class="service-item position-relative text-center"> <!-- Added text-center -->
              <div class="icon">
                <i class="bi bi-tools" style="color: #0dcaf0;"></i> 
              </div>
              <a href="maintainance.php" class="stretched-link">
                <h3>Maintenance Order</h3>
              </a>
              <p>Submit maintenance requests for facility repairs, ensuring a safe and well-maintained environment.</p>
            </div>
          </div>
      
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
            <div class="service-item position-relative text-center"> <!-- Added text-center -->
              <div class="icon">
                <i class="bi bi-clipboard-check" style="color: #fd7e14;"></i> 
              </div>
              <a href="maintainancecheck.php" class="stretched-link">
                <h3>Check Maintenance</h3>
              </a>
              <p>Review and monitor submitted reports to stay updated on maintenance issues and facility conditions.</p>
            </div>
          </div>
        </div>
      </div>

<div class="container mt-5 text-center" data-aos="fade-up" data-aos-duration="1000">
    <div class="dashboard-box">
        <h2 class="mb-4">User Dashboard</h2>
        <div class="d-flex justify-content-center align-items-center gap-3" style="flex-direction: row;">
        <div class="d-flex flex-column align-items-center">
                <label for="month" class="fw-bold">Select Month:</label>
                <select id="month" class="form-select w-100 mb-2">
                <option value="all">All</option>
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>

                <label for="year" class="fw-bold">Select Year:</label>
                <select id="year" class="form-select w-100">
                <option value="all">All</option>
                    <option value="2025">2025</option>
                    <option value="2026">2026</option>
                    <option value="2027">2027</option>
                    <option value="2028">2028</option>
                    <option value="2029">2029</option>
                    <option value="2030">2030</option>
                </select>
            </div>
        <canvas id="statusChart" style="max-width: 400px; max-height: 300px;" data-aos="fade-up" data-aos-delay="300"></canvas>
        </div>
    </div>
</div>

    </section>
  </main>

  <footer id="footer" class="footer dark-background">
    <div class="container">
      <h3 class="sitename">Amari Johor Bahru</h3>
      <p>Providing the best maintenance order management for seamless workflow and efficient service.</p>
      <div class="social-links d-flex justify-content-center">
        <a href=""><i class="bi bi-twitter-x"></i></a>
        <a href=""><i class="bi bi-facebook"></i></a>
        <a href=""><i class="bi bi-instagram"></i></a>
        <a href=""><i class="bi bi-skype"></i></a>
        <a href=""><i class="bi bi-linkedin"></i></a>
      </div>
      <div class="container">
        <div class="copyright">
        <span>Copyright</span> <strong class="px-1 sitename">Amari Johor Bahru</strong> <span>All Rights Reserved</span>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>


  <!-- Vendor JS Files -->
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/vendor/php-email-form/validate.js"></script>
  <script src="../assets/vendor/aos/aos.js"></script>
  <script src="../assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="../assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="../assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="../assets/vendor/swiper/swiper-bundle.min.js"></script><!-- Simpan JavaScript dalam file terpisah -->

  <!-- Main JS File -->
  <script src="../assets/js/main.js"></script>

</body>

</html>