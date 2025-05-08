<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Share</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            background-color: #f4f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background: linear-gradient(90deg, #007bff, #00d4ff);
            color: white;
            padding: 2rem;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            margin-bottom: 2rem;
        }
        .file-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            background: white;
            margin-bottom: 1.5rem;
        }
        .file-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        .file-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .file-card .card-body {
            padding: 1.5rem;
        }
        .file-icon {
            font-size: 2rem;
            color: #007bff;
        }
        .container {
            max-width: 1200px;
        }
        .btn-download {
            background-color: #28a745;
            color: white;
            border: none;
            transition: background-color 0.3s;
        }
        .btn-download:hover {
            background-color: #218838;
        }
        .btn-logout {
            background: linear-gradient(90deg, #dc3545, #ff6b6b);
            color: white;
            border: none;
            border-radius: 20px;
            padding: 0.5rem 1rem;
        }
        .btn-logout:hover {
            opacity: 0.9;
        }
        .search-container {
            margin-bottom: 2rem;
        }
        .search-container .form-control {
            border-radius: 20px;
            padding-left: 2.5rem;
        }
        .search-container .input-group-text {
            background: transparent;
            border: none;
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
        }
        @media (max-width: 576px) {
            .file-card img {
                height: 100px;
            }
            .file-card .card-body {
                padding: 1rem;
            }
            .search-container .form-control {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1>File Share Hub</h1>
                    <p>Browse and download shared files with ease!</p>
                </div>
                <a href="/logout" class="btn btn-logout"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
            </div>
        </div>
        <div class="search-container">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="search-input" placeholder="Search files..." aria-label="Search files">
            </div>
        </div>
        <div class="row" id="file-list">
            <!-- Files will be populated here -->
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        let allFiles = [];
        function getFileIcon(type) {
            switch (type.toLowerCase()) {
                case 'pdf': return '<i class="fas fa-file-pdf file-icon"></i>';
                case 'image': return '<i class="fas fa-file-image file-icon"></i>';
                case 'video': return '<i class="fas fa-file-video file-icon"></i>';
                default: return '<i class="fas fa-file file-icon"></i>';
            }
        }
        function populateFiles(files) {
            const fileList = document.getElementById('file-list');
            fileList.innerHTML = '';
            if (files.length === 0) {
                fileList.innerHTML = '<div class="col-12 text-center text-muted">No files found.</div>';
                return;
            }
            files.forEach(file => {
                const card = document.createElement('div');
                card.className = 'col-md-4 col-sm-6';
                card.innerHTML = `
                    <div class="file-card card">
                        ${file.type === 'image' ? `<img src="${file.path}" alt="${file.name}" class="card-img-top">` : ''}
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                ${getFileIcon(file.type)}
                                <h5 class="card-title ms-2 mb-0">${file.name}</h5>
                            </div>
                            <p class="card-text text-muted">Size: ${file.size}</p>
                            <a href="${file.path}" class="btn btn-download btn-sm" download>Download</a>
                        </div>
                    </div>
                `;
                fileList.appendChild(card);
            });
        }
        function filterFiles(searchTerm) {
            const filteredFiles = allFiles.filter(file =>
                file.name.toLowerCase().includes(searchTerm.toLowerCase())
            );
            populateFiles(filteredFiles);
        }
        fetch('/list_files.php', { credentials: 'same-origin' })
            .then(response => response.json())
            .then(data => {
                allFiles = data;
                populateFiles(allFiles);
            })
            .catch(error => console.error('Error fetching files:', error));
        document.getElementById('search-input').addEventListener('input', (e) => {
            const searchTerm = e.target.value.trim();
            filterFiles(searchTerm);
        });
        document.addEventListener('DOMContentLoaded', () => {});
    </script>
</body>
</html>
