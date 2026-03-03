<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dictionary Hub</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
<div class="container">
<a class="navbar-brand fw-bold" href="index.php">📖 DictionaryHub</a>

<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
<span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse" id="navbarNav">
<ul class="navbar-nav me-auto">
<li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
<li class="nav-item"><a class="nav-link" href="index.php?page=catalog">Catalog</a></li>
<li class="nav-item"><a class="nav-link" href="index.php?page=search">Search</a></li>
<li class="nav-item"><a class="nav-link" href="index.php?page=preferences">Preferences</a></li>
</ul>

<button onclick="toggleTheme()" class="btn btn-outline-light">
Toggle Theme
</button>
</div>
</div>
</nav>