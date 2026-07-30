<?php

session_start();

if(!isset($_SESSION["login"])) {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../function/koneksi.php'; 
require_once __DIR__ . '/../function/copyright.php'; 

$donatur =  query("SELECT * FROM donatur");


?>



<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Admin Dashboard - Manage Donations</title>
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
        .dropdown:hover .dropdown-menu {
            display: block;
        }
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .btn {
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            width: 100px;
        }
        .btn:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
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
            <a class="text-gray-800 hover:text-blue-600 slide-in" href="https://www.instagram.com/mooneloz.id?igsh=bGhvcnRyMTM1aHEw ">Contact</a>
            <a class="text-gray-800 hover:text-blue-600 slide-in" href="/donasi/tambah_donasi.php">Donate</a>
            <a class="text-gray-800 hover:text-blue-600 slide-in" href="../function/logout.php">Logout</a>
        </div>
        <div class="md:hidden relative dropdown">
            <button class="text-gray-800 focus:outline-none hover:text-blue-600" id="menu-button">
                <i class="fas fa-bars"></i>
            </button>
            <div class="dropdown-menu hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-20" id="mobile-menu">
                <a class="block px-4 py-2 text-gray-800 hover:bg-gray-100" href="/donasi/index.php">Home</a>
                <a class="block px-4 py-2 text-gray-800 hover:bg-gray-100" href="/donasi/about.php">About</a>
                <a class="block px-4 py-2 text-gray-800 hover:bg-gray-100" href="https://www.instagram.com/mooneloz.id?igsh=bGhvcnRyMTM1aHEw">Contact</a>
                <a class="block px-4 py-2 text-gray-800 hover:bg-gray-100" href="/donasi/tambah_donasi.php">Donate</a>
                <a class="block px-4 py-2 text-gray-800 hover:bg-gray-100" href="../function/logout.php">Logout</a>
            </div>
        </div>
    </div>
</nav>
<main class="container mx-auto p-4">
    <div class="bg-white shadow-lg rounded-lg p-6 mb-6 relative fade-in">
        <div class="relative z-10">
            <h1 class="text-3xl font-bold mb-4 text-gray-800 bounce-in">Manage Donations</h1>
            <p class="text-gray-700 mb-6 slide-in">Admin dapat mengelola donasi dengan melakukan edit atau hapus pada catatan di bawah ini.</p>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded-lg">
                    <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">No</th>
                        <th class="py-3 px-6 text-left">Nama Donatur</th>
                        <th class="py-3 px-6 text-left">Nominal</th>
                        <th class="py-3 px-6 text-left">Metode Pembayaran</th>
                        <th class="py-3 px-6 text-left">Tgl Diterima</th>
                        <th class="py-3 px-6 text-left">Bukti Donasi</th>
                        <th class="py-3 px-6 text-left">Deskripsi</th>
                        <th class="py-3 px-6 text-left">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <?php $i = 1; ?>
                        <?php foreach($donatur as $row) : ?>
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 text-left"><?= $i; ?></td>
                        <td class="py-3 px-6 text-left"><?= $row["nama"]; ?></td>
                        <td class="py-3 px-6 text-left"><?= $row["email"]; ?></td>
                        <td class="py-3 px-6 text-left"><?= $row["nominal"]; ?></td>
                        <td class="py-3 px-6 text-left"><?= $row["metode"]; ?></td>
                        <td class="py-3 px-6 text-left"><?= $row["tanggal_diterima"]; ?></td>
                        <td class="py-3 px-6 text-left">
                            <img src="../img/<?= $row["bukti"]; ?>"  height="100"  width="100" class="cursor-pointer" id="donation-image" />
                        </td>
                        <td class="py-3 px-6 text-left"><?= $row["deskripsi"]; ?></td>
                        <td class="py-3 px-6 text-left">
                            <button class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow-md hover:bg-blue-600 transition duration-300 btn mb-2">
                                <i class="fas fa-edit"></i> <a href="edit.php?id=<?= $row["id"]; ?>">Edit</a>
                            </button>
                            <button class="bg-red-500 text-white px-4 py-2 rounded-lg shadow-md hover:bg-red-600 transition duration-300 btn">
                                <i class="fas fa-trash"></i> <a href="hapus.php?id=<?= $row["id"]; ?>"  onclick="return confirm('yakin?');">Delete</a>
                            </button>
                        </td>
                        <?php $i++; ?>
                        <?php endforeach; ?>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
<footer class="bg-gray-900 text-white mt-6">
    <div class="container mx-auto p-4 flex flex-col md:flex-row justify-between items-center">
        <p class="text-gray-400">&copy; <?= $copyright; ?></p>
        <div class="flex space-x-4 mt-4 md:mt-0">
            <a class="text-gray-400 hover:text-white" href="notfound.html"><i class="fab fa-facebook-f"></i></a>
            <a class="text-gray-400 hover:text-white" href="notfound.html"><i class="fab fa-twitter"></i></a>
            <a class="text-gray-400 hover:text-white" href="https://www.instagram.com/mooneloz.id?igsh=bGhvcnRyMTM1aHEw"  target="_blank"><i class="fab fa-instagram"></i></a>
            <a class="text-gray-400 hover:text-white" href=""  target="_blank"><i class="fab fa-linkedin-in"></i></a>
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