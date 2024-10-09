<?php
session_start(); // Make sure this is the first line in the file

if (!isset($_SESSION['id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

echo "
    <script type='text/javascript'>
        alert('Your transaction has been successful!');
        window.location.href = 'index.php';
    </script>
";
