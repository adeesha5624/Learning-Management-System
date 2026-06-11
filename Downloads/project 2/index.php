<?php

$pageTitle = "Welcome to NDT Manager";
include 'config/db.php';
include 'includes/header.php'; 

try {
    $stmt = $pdo->prepare("SELECT title, date, venue FROM events ORDER BY date ASC LIMIT 3");
    $stmt->execute();
    $upcoming_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $upcoming_events = [];
    $error_message = "Could not fetch events: " . $e->getMessage();
}
?>
<?php
?>
<header class="relative w-full h-72 md:h-96 rounded-xl overflow-hidden mb-6">
    <img src="assets/images/event_banner.jpg" alt="Site cover" class="absolute inset-0 w-full h-full object-cover object-center">
    <div class="absolute inset-0 bg-black bg-opacity-40"></div>
    <div class="relative z-10 max-w-4xl mx-auto px-6 py-12 md:py-20 text-center text-white">
        <h1 class="text-3xl md:text-5xl font-extrabold leading-tight"><?php echo htmlspecialchars($pageTitle); ?></h1>
        <p class="mt-4 text-lg md:text-xl opacity-90">
            Your central hub for all academic and extracurricular events at the university.
        </p>
        <div class="mt-6">
            <a href="events.php" class="inline-block bg-red-900 text-white font-semibold py-3 px-8 rounded-full shadow-lg hover:bg-indigo-700 transition duration-300 transform hover:scale-105">
                View All Events
            </a>
        </div>
    </div>
</header>


<section>
    <h3 class="text-3xl font-bold text-gray-800 mb-6">Upcoming Highlights</h3>
    <?php if (isset($error_message)): ?>
        <p class="text-red-500 bg-red-100 p-3 rounded-md"><?php echo $error_message; ?></p>
    <?php elseif (empty($upcoming_events)): ?>
        <p class="text-gray-600 p-4 bg-yellow-50 rounded-lg">No upcoming events are currently scheduled. Check back soon!</p>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($upcoming_events as $event): ?>
                    <?php
                        $cardColors = ['border-red-900','border-green-500','border-yellow-500','border-pink-500','border-purple-500'];
                        $ci = 0;
                        $colorClass = $cardColors[$ci % count($cardColors)];
                    ?>
                    <div class="event-card bg-white p-4 rounded-lg shadow border-l-8 <?php echo $colorClass; ?>">
                        <h4 class="text-xl font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($event['title']); ?></h4>
                        <p class="text-sm text-gray-500 mb-3">
                            <span class="font-bold">Date:</span> <?php echo date('F jS, Y', strtotime($event['date'])); ?><br>
                            <span class="font-bold">Venue:</span> <?php echo htmlspecialchars($event['venue']); ?>
                        </p>
                        <a href="/project/events.php" class="text-sm text-gray-700 hover:text-gray-900 font-medium">Find out more &rarr;</a>
                    </div>
                    <?php $ci++; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
