<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Event Management | <?php echo $pageTitle ?? 'Home'; ?></title>
    <link rel="stylesheet" href="/project/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <header class="bg-red-900 shadow-md">
        <div class="container mx-auto p-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-white"><a href="/project/index.php">NDT Manager</a></h1>
            <nav>
                <ul class="flex space-x-6 text-white font-medium">
                    <li><a href="/project/index.php" class="hover:text-indigo-200 transition duration-150">Home</a></li>
                    <li><a href="/project/events.php" class="hover:text-indigo-200 transition duration-150">Events</a></li>
                    
                    <?php if ($is_admin): ?>
                        <li><a href="/project/admin/dashboard.php" class="bg-indigo-500 px-3 py-1 rounded-full hover:bg-indigo-400 transition duration-150">Admin Dashboard</a></li>
                    <?php endif; ?>
                    
                    <?php if ($is_logged_in): ?>
                        <li><a href="/project/logout.php" class="hover:text-indigo-200 transition duration-150">Logout</a></li>
                    <?php else: ?>
                        <li><a href="/project/register.php" class="hover:text-indigo-200 transition duration-150">Login/Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container mx-auto p-4 py-8">
