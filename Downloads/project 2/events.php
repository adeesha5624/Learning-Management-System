<?php
$pageTitle = "Event Listing";
include 'config/db.php';
include 'includes/header.php';

if (session_status() == PHP_SESSION_NONE) { session_start(); }
$user_id = $_SESSION['user_id'] ?? null;
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id']) && $user_id) {
    $event_id = filter_var($_POST['event_id'], FILTER_VALIDATE_INT);
    $contact_number = filter_var($_POST['contact_number'], FILTER_SANITIZE_STRING);

    if ($event_id) {
        try {
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE user_id = :user_id AND event_id = :event_id");
            $checkStmt->execute([':user_id' => $user_id, ':event_id' => $event_id]);
            if ($checkStmt->fetchColumn() > 0) {
                $message = "You are already registered for this event.";
                $message_type = 'warning';
            } else {
                $stmt = $pdo->prepare("INSERT INTO registrations (user_id, event_id, contact_number) VALUES (:user_id, :event_id, :contact_number)");
                $stmt->execute([
                    ':user_id' => $user_id,
                    ':event_id' => $event_id,
                    ':contact_number' => $contact_number
                ]);
                $message = "Successfully registered for the event! Check your email for confirmation (hypothetically).";
                $message_type = 'success';
            }
        } catch (PDOException $e) {
            $message = "Registration failed: " . $e->getMessage();
            $message_type = 'error';
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM events ORDER BY date ASC");
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $events = [];
    $message = "Error retrieving events: " . $e->getMessage();
    $message_type = 'error';
}
?>

<h2 class="text-4xl font-bold text-gray-900 mb-8 border-b pb-2">All Available Events</h2>

<?php if ($message): ?>
    <div class="p-4 mb-6 rounded-lg text-white font-medium 
        <?php echo $message_type === 'success' ? 'bg-green-500' : ($message_type === 'warning' ? 'bg-yellow-500' : 'bg-red-500'); ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php if (empty($events)): ?>
    <p class="text-gray-600 p-6 bg-yellow-50 rounded-lg">No events are currently listed.</p>
<?php else: ?>
    <div class="space-y-8">
        <?php foreach ($events as $event): ?>
            <div class="event-card flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="mb-4 md:mb-0 md:w-3/4">
                    <h3 class="text-2xl font-bold text-red-900"><?php echo htmlspecialchars($event['title']); ?></h3>
                    <p class="text-gray-600 mt-1 mb-3"><?php echo htmlspecialchars($event['description']); ?></p>
                    <div class="text-sm text-gray-500 space-y-1">
                        <p><span class="font-semibold text-gray-700">Date:</span> <?php echo date('F jS, Y', strtotime($event['date'])); ?></p>
                        <p><span class="font-semibold text-gray-700">Venue:</span> <?php echo htmlspecialchars($event['venue']); ?></p>
                    </div>
                </div>

                <div class="md:w-1/4 text-right">
                    <?php if ($user_id): ?>
                        <button onclick="document.getElementById('reg_form_<?php echo $event['event_id']; ?>').classList.toggle('hidden')" 
                                class="bg-green-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-600 transition duration-150">
                            Register Now
                        </button>
                    <?php else: ?>
                        <p class="text-sm text-red-500 font-semibold">
                            <a href="register.php" class="underline">Login</a> to Register
                        </p>
                    <?php endif; ?>
                </div>

                <?php if ($user_id): ?>
                <div id="reg_form_<?php echo $event['event_id']; ?>" class="hidden mt-4 pt-4 border-t border-gray-200 w-full">
                    <h4 class="text-xl font-semibold mb-3">Register for <?php echo htmlspecialchars($event['title']); ?></h4>
                    <form action="events.php" method="POST" id="event-registration-form">
                        <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                        
                        <div class="form-group md:w-1/2">
                            <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number" class="form-input" required>
                            <p id="contact_number-error" class="error-message"></p>
                        </div>
                        
                        <button type="submit" class="mt-4 bg-red-900 text-white font-semibold py-2 px-6 rounded-lg hover:bg-red-900 transition duration-150">
                            Confirm Registration
                        </button>
                    </form>
                </div>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
