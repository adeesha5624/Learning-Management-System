<?php
$pageTitle = "Admin Dashboard";
include '../config/db.php';
include '../includes/header.php';

if (session_status() == PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';
$edit_event = null; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_announcement'])) {
    $email_subject = trim($_POST['email_subject'] ?? '');
    $email_body = trim($_POST['email_body'] ?? '');

    if (empty($email_subject) || empty($email_body)) {
        $message = 'Subject and message body are required to send announcements.';
        $message_type = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT email, name FROM users WHERE email IS NOT NULL AND email != ''");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sent = 0;
            $failed = 0;

            $from_email = 'no-reply@localhost';
            if (defined('MAIL_FROM') && MAIL_FROM) {
                $from_email = MAIL_FROM;
            }
            $headers = "From: " . $from_email . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            foreach ($users as $u) {
                $to = $u['email'];
                $personal_body = "<p>Dear " . htmlspecialchars($u['name']) . ",</p>" . $email_body;

                if (mail($to, $email_subject, $personal_body, $headers)) {
                    $sent++;
                } else {
                    $failed++;
                }
            }

            $message = "Announcement sent. Successful: $sent. Failed: $failed.";
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Failed to send announcement: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['create_event']) || isset($_POST['update_event']))) {
    $is_update = isset($_POST['update_event']);
    $event_id = $is_update ? filter_var($_POST['event_id'], FILTER_VALIDATE_INT) : null;
    $title = trim($_POST['title']);
    $date = trim($_POST['date']);
    $venue = trim($_POST['venue']);
    $description = trim($_POST['description']);

    if (empty($title) || empty($date) || empty($venue)) {
        $message = "Title, Date, and Venue are required.";
        $message_type = 'error';
    } else {
        try {
            if ($is_update && $event_id) {
                $stmt = $pdo->prepare("UPDATE events SET title = :title, date = :date, venue = :venue, description = :description WHERE event_id = :event_id");
                $stmt->execute([
                    ':title' => $title,
                    ':date' => $date,
                    ':venue' => $venue,
                    ':description' => $description,
                    ':event_id' => $event_id
                ]);
                $message = "Event updated successfully!";
                $message_type = 'success';
            } else {
                $stmt = $pdo->prepare("INSERT INTO events (title, date, venue, description, organizer_id) VALUES (:title, :date, :venue, :description, :organizer_id)");
                $stmt->execute([
                    ':title' => $title,
                    ':date' => $date,
                    ':venue' => $venue,
                    ':description' => $description,
                    ':organizer_id' => $user_id
                ]);
                $message = "New event created successfully!";
                $message_type = 'success';
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $message_type = 'error';
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $event_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($event_id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = :event_id");
            $stmt->execute([':event_id' => $event_id]);
            $message = "Event deleted successfully.";
            $message_type = 'success';
            header("Location: dashboard.php");
            exit();
        } catch (PDOException $e) {
            $message = "Deletion failed: " . $e->getMessage();
            $message_type = 'error';
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $event_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($event_id) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = :event_id");
            $stmt->execute([':event_id' => $event_id]);
            $edit_event = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$edit_event) {
                $message = "Event not found.";
                $message_type = 'error';
            }
        } catch (PDOException $e) {
            $message = "Error fetching event for editing: " . $e->getMessage();
            $message_type = 'error';
        }
    }
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            e.event_id, e.title, e.date, e.venue,
            COUNT(r.reg_id) AS participant_count
        FROM events e
        LEFT JOIN registrations r ON e.event_id = r.event_id
        GROUP BY e.event_id
        ORDER BY e.date ASC
    ");
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $events = [];
    $message = "Error retrieving events list: " . $e->getMessage();
    $message_type = 'error';
}

function get_participants($pdo, $event_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT u.name, u.student_id, u.email, r.contact_number, r.timestamp 
            FROM registrations r
            JOIN users u ON r.user_id = u.user_id
            WHERE r.event_id = :event_id
            ORDER BY r.timestamp ASC
        ");
        $stmt->execute([':event_id' => $event_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}
?>

<h2 class="text-4xl font-bold text-gray-900 mb-8 border-b pb-2">Admin Dashboard</h2>

<?php if ($message): ?>
    <div class="p-4 mb-6 rounded-lg text-white font-medium 
        <?php echo $message_type === 'success' ? 'bg-green-500' : 'bg-red-500'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<section class="mb-8 p-6 bg-white rounded-xl shadow-lg border-t-4 border-yellow-500">
    <h3 class="text-2xl font-semibold mb-4 text-yellow-700">Send Announcement to All Users</h3>
    <form action="dashboard.php" method="POST">
        <input type="hidden" name="send_announcement" value="1">
        <div class="form-group mb-4">
            <label for="email_subject" class="block text-sm font-medium text-gray-700">Subject</label>
            <input type="text" id="email_subject" name="email_subject" class="form-input w-full" required>
        </div>
        <div class="form-group mb-4">
            <label for="email_body" class="block text-sm font-medium text-gray-700">Message (HTML allowed)</label>
            <textarea id="email_body" name="email_body" rows="4" class="form-input w-full" required></textarea>
        </div>
        <button type="submit" class="bg-yellow-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-yellow-700 transition duration-150">Send Announcement</button>
    </form>
</section>

<section class="mb-10 p-6 bg-white rounded-xl shadow-lg border-t-4 border-indigo-600">
    <h3 class="text-2xl font-semibold mb-4 text-indigo-700">
        <?php echo $edit_event ? 'Edit Event: ' . htmlspecialchars($edit_event['title']) : 'Create New Event'; ?>
    </h3>
    <form action="dashboard.php" method="POST">
        <?php if ($edit_event): ?>
            <input type="hidden" name="event_id" value="<?php echo $edit_event['event_id']; ?>">
            <input type="hidden" name="update_event" value="1">
        <?php else: ?>
            <input type="hidden" name="create_event" value="1">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="form-group">
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" id="title" name="title" class="form-input" required value="<?php echo $edit_event ? htmlspecialchars($edit_event['title']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                <input type="date" id="date" name="date" class="form-input" required value="<?php echo $edit_event ? htmlspecialchars($edit_event['date']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="venue" class="block text-sm font-medium text-gray-700">Venue</label>
                <input type="text" id="venue" name="venue" class="form-input" required value="<?php echo $edit_event ? htmlspecialchars($edit_event['venue']) : ''; ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea id="description" name="description" rows="3" class="form-input"><?php echo $edit_event ? htmlspecialchars($edit_event['description']) : ''; ?></textarea>
        </div>

        <button type="submit" class="bg-indigo-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-150">
            <?php echo $edit_event ? 'Save Changes' : 'Publish Event'; ?>
        </button>
        <?php if ($edit_event): ?>
            <a href="dashboard.php" class="ml-4 text-gray-600 hover:text-gray-900">Cancel Edit</a>
        <?php endif; ?>
    </form>
</section>

<section>
    <h3 class="text-3xl font-bold text-gray-800 mb-6">Manage Events & View Registrations</h3>
    
    <div class="space-y-6">
        <?php if (empty($events)): ?>
            <p class="text-gray-600 p-4 bg-yellow-50 rounded-lg">No events to manage.</p>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <div class="p-6 bg-white rounded-xl shadow-lg border-l-8 border-green-500">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($event['title']); ?></h4>
                            <p class="text-sm text-gray-500">
                                <?php echo date('F jS, Y', strtotime($event['date'])); ?> at <?php echo htmlspecialchars($event['venue']); ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-bold text-green-600"><?php echo $event['participant_count']; ?></span>
                            <p class="text-sm text-gray-500">Registered</p>
                        </div>
                    </div>

                    <div class="flex space-x-3 mb-4 border-t pt-3">
                        <a href="dashboard.php?action=edit&id=<?php echo $event['event_id']; ?>" 
                           class="text-sm bg-blue-500 text-white py-1 px-3 rounded hover:bg-blue-600 transition">Edit</a>
                        <a href="dashboard.php?action=delete&id=<?php echo $event['event_id']; ?>" 
                           onclick="return confirm('Are you sure you want to delete this event and all its registrations?')" 
                           class="text-sm bg-red-500 text-white py-1 px-3 rounded hover:bg-red-600 transition">Delete</a>
                        <button onclick="document.getElementById('participants_<?php echo $event['event_id']; ?>').classList.toggle('hidden')"
                                class="text-sm bg-gray-500 text-white py-1 px-3 rounded hover:bg-gray-600 transition">
                            View Participants
                        </button>
                    </div>

                    <div id="participants_<?php echo $event['event_id']; ?>" class="hidden mt-4 bg-gray-50 p-4 rounded-lg">
                        <h5 class="text-lg font-semibold mb-2">Participants for <?php echo htmlspecialchars($event['title']); ?></h5>
                        <?php $participants = get_participants($pdo, $event['event_id']); ?>
                        
                        <?php if (empty($participants)): ?>
                            <p class="text-gray-500">No participants registered yet.</p>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name (ID)</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($participants as $p): ?>
                                            <tr>
                                                <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($p['name']); ?> (<?php echo htmlspecialchars($p['student_id']); ?>)
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($p['email']); ?></td>
                                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($p['contact_number'] ?? 'N/A'); ?></td>
                                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500"><?php echo date('Y-m-d H:i', strtotime($p['timestamp'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
