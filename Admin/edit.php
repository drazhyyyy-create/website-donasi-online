<?php

session_start();

if(!isset($_SESSION["login"])) {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../function/koneksi.php'; 
require_once __DIR__ . '/../function/f_edit.php'; 
require_once __DIR__ . '/../function/copyright.php'; 


$id = $_GET["id"];

$donatur = query("SELECT * FROM donatur WHERE id = $id")[0];

if(isset($_POST["submit"])) {
  
    if(edit($_POST,$id) > 0) {
        echo "
        <script>
        alert('data berhasil diubah!');
        document.location.href = 'manage_donasi.php'; 
        </script>
        ";
    } else {
        echo "
        <script>
        alert('data gagal diubah!');
        document.location.href = 'manage_donasi.php'; 
        </script>
        ";
    }

}



?>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Edit Donation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f3f4f6;
        }
        main {
            flex: 1;
        }
        .fade-in {
            animation: fadeIn 1s ease-in-out;
        }
        .slide-in {
            animation: slideIn 1s ease-in-out;
        }
        .bounce-in {
            animation: bounceIn 1s ease-in-out;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        @keyframes slideIn {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes bounceIn {
            0% {
                transform: scale(0.5);
                opacity: 0;
            }
            60% {
                transform: scale(1.2);
                opacity: 1;
            }
            100% {
                transform: scale(1);
            }
        }

          /* Modal styles */
          .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            margin: auto;
            display: block;
            max-width: 80%;
            max-height: 80%;
        }
        .close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
<nav class="bg-white shadow-md">
    <div class="container mx-auto p-4 flex justify-between items-center">
        <a class="text-2xl font-bold text-gray-800 slide-in" href="#">
            AyoDonasi
        </a>
        <div class="hidden md:flex space-x-4">
            <a class="text-gray-800 hover:text-blue-600 slide-in" href="/donasi/index.php">Home</a>
            <a class="text-gray-800 hover:text-blue-600 slide-in" href="/donasi/about.php">About</a>
            <a class="text-gray-800 hover:text-blue-600 slide-in" href="https://ribin.my.id/">Contact</a>
            <a class="text-gray-800 hover:text-blue-600 slide-in" href="/donasi/tambah_donasi.php">Donate</a>
            <a class="text-gray-800 hover:text-blue-600 slide-in" href="/donasi/login.php">Logout</a>
        </div>
        <div class="md:hidden relative dropdown">
            <button class="text-gray-800 focus:outline-none hover:text-blue-600" id="menu-button">
                <i class="fas fa-bars"></i>
            </button>
            <div class="dropdown-menu hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-20" id="mobile-menu">
                <a class="block px-4 py-2 text-gray-800 hover:bg-gray-100" href="/donasi/index.php">Home</a>
                <a class="block px-4 py-2 text-gray-800 hover:bg-gray-100" href="/donasi/about.php">About</a>
                <a class="block px-4 py-2 text-gray-800 hover:bg-gray-100" href="https://ribin.my.id/">Contact</a>
                <a class="block px-4 py-2 text-gray-800 hover:bg-gray-100" href="/donasi/tambah_donasi.php">Donate</a>
                <a class="block px-4 py-2 text-gray-800 hover:bg-gray-100" href="/donasi/login.php">Logout</a>
            </div>
        </div>
    </div>
</nav>
<main class="container mx-auto p-4">
    <div class="bg-white shadow-lg rounded-lg p-6 mb-6 relative fade-in">
        <div class="relative z-10">
            <h1 class="text-3xl font-bold mb-4 text-gray-800 bounce-in">Edit Donasi</h1>
            <p class="text-gray-700 mb-6 slide-in">Edit rincian donasi di bawah ini.</p>
            <form action="" method="post" enctype="multipart/form-data">
            
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="nama">
                        Nama Donatur
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="nama" name="nama" type="text" required value="<?= $donatur["nama"]; ?>">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="nominal">
                        Nominal
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="nominal" name="nominal" type="number" required value="<?= $donatur["nominal"]; ?>">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="metode">
                        Metode Pembayaran
                    </label>
                    <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="metode" name="metode"   >
                    <option  required value="<?= $donatur["metode"]; ?>">
                    <?= $donatur["metode"]; ?> (saat ini)
                    </option>
                        <option value="ShopeePay" name="metode" type="radio">ShopeePay</option>
                        <option value="gopay" name="metode" type="radio">gopay</option>
                        <option value="Transfer" name="metode" type="radio">Transfer</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="tanggal_diterima">
                        Tgl Diterima
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-200 cursor-not-allowed" id="tanggal_diterima" name="tanggal_diterima" value="<?= $donatur["tanggal_diterima"]; ?>" readonly> <!--readonly biar gk bis diubah2-->
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="bukti">
                        Bukti Donasi
                    </label>
                    <img src="../img/<?= $donatur["bukti"]; ?>" name="bukti" class="cursor-pointer" id="donation-image" class="mt-2" width="100" height="100">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="deskripsi">
                        Deskripsi
                    </label>
                    <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="deskripsi" name="deskripsi"> <?= $donatur["deskripsi"]; ?></textarea> <!--text aera gk ush pake value-->
                </div>
                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit" name="submit">
                        Update
                    </button>
                    <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="manage_donasi.php">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>
<footer class="bg-gray-900 text-white mt-6">
    <div class="container mx-auto p-4 flex flex-col md:flex-row justify-between items-center">
    <p class="text-gray-400">&copy; <?= $copyright; ?></p>
        <div class="flex space-x-4 mt-4 md:mt-0">
            <a class="text-gray-400 hover:text-white" href="notfound.html"><i class="fab fa-facebook-f"></i></a>
            <a class="text-gray-400 hover:text-white" href="notfound.html"><i class="fab fa-twitter"></i></a>
            <a class="text-gray-400 hover:text-white" href="https://www.instagram.com/bintangrrrrrr/" target="_blank"><i class="fab fa-instagram"></i></a>
            <a class="text-gray-400 hover:text-white" href="https://www.linkedin.com/in/bintangro/" target="_blank"><i class="fab fa-linkedin-in"></i></a>
        </div>
    </div>
</footer>
<!-- Modal for image display -->
<div id="imageModal" class="modal">
    <span class="close" id="closeModal">&times;</span>
    <img class="modal-content" id="modalImage" />
</div>
<script>
    document.getElementById('menu-button').addEventListener('click', function() {
        var menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    });

    // Add fade-in effect to elements
    document.addEventListener('DOMContentLoaded', function() {
        const fadeInElements = document.querySelectorAll('.fade-in');
        fadeInElements.forEach(element => {
            element.classList.add('opacity-0');
            setTimeout(() => {
                element.classList.remove('opacity-0');
            }, 100);
        });
    });

    // Add scroll-to-top button
    const scrollToTopButton = document.createElement('button');
    scrollToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
    scrollToTopButton.classList.add('fixed', 'bottom-4', 'right-4', 'bg-blue-600', 'text-white', 'p-3', 'rounded-full', 'shadow-md', 'hover:bg-blue-700', 'transition', 'duration-300', 'hidden');
    document.body.appendChild(scrollToTopButton);

    scrollToTopButton.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    window.addEventListener('scroll', () => {
        if (window.scrollY > 200) {
            scrollToTopButton.classList.remove('hidden');
        } else {
            scrollToTopButton.classList.add('hidden');
        }
    });

       // Modal functionality
       const donationImages = document.querySelectorAll('img.cursor-pointer');
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const closeModal = document.getElementById('closeModal');

donationImages.forEach(image => {
    image.addEventListener('click', function() {
        modal.style.display = "flex";
        modalImage.src = this.src;
    });
});

closeModal.onclick = function() {
    modal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target === modal) {
        modal.style.display = "none";
    }
}
</script>
</body>
</html>