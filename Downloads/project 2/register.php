<?php
$pageTitle = "Login/Register";
include 'config/db.php';
include 'includes/header.php';

if (session_status() == PHP_SESSION_NONE) { session_start(); }
$message = '';
$message_type = '';

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $student_id = trim($_POST['student_id']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($name) || empty($student_id) || empty($email) || empty($password)) {
        $message = "All fields are mandatory.";
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $message_type = 'error';
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email OR student_id = :student_id");
            $checkStmt->execute([':email' => $email, ':student_id' => $student_id]);
            
            if ($checkStmt->fetchColumn() > 0) {
                $message = "Email or Student ID already exists.";
                $message_type = 'warning';
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (name, student_id, email, password_hash) VALUES (:name, :student_id, :email, :password_hash)");
                $stmt->execute([
                    ':name' => $name,
                    ':student_id' => $student_id,
                    ':email' => $email,
                    ':password_hash' => $password_hash
                ]);
                
                $message = "Registration successful! You can now log in.";
                $message_type = 'success';
            }
        } catch (PDOException $e) {
            $message = "Registration failed: " . $e->getMessage();
            $message_type = 'error';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']); 

    try {
        $stmt = $pdo->prepare("SELECT user_id, password_hash, is_admin FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            if ($user['is_admin']) {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: events.php");
            }
            exit();
        } else {
            $message = "Invalid email or password.";
            $message_type = 'error';
        }
    } catch (PDOException $e) {
        $message = "Login failed: " . $e->getMessage();
        $message_type = 'error';
    }
}
?>

<h2 class="text-4xl font-bold text-gray-900 mb-8 border-b pb-2">User Authentication</h2>

<?php if ($message): ?>
    <div class="p-4 mb-6 rounded-lg text-white font-medium 
        <?php echo $message_type === 'success' ? 'bg-green-500' : ($message_type === 'warning' ? 'bg-yellow-500' : 'bg-red-500'); ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-10">
    <div class="p-6 bg-white rounded-xl shadow-lg">
        <h3 class="text-2xl font-semibold text-indigo-700 mb-4">Login to Your Account</h3>
        <form action="register.php" method="POST" id="user-login-form">
            <input type="hidden" name="login" value="1">
            
            <div class="form-group">
                <label for="login_email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" id="login_email" name="email" class="form-input" required>
                <p id="login_email-error" class="error-message"></p>
            </div>
            
            <div class="form-group">
                <label for="login_password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="login_password" name="password" class="form-input" required>
                <p id="login_password-error" class="error-message"></p>
            </div>
            
            <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-indigo-700 transition duration-150">
                Login
            </button>
        </form>
    </div>

    <div class="p-6 bg-white rounded-xl shadow-lg">
        <h3 class="text-2xl font-semibold text-green-700 mb-4">New User Registration</h3>
        <form action="register.php" method="POST" id="user-register-form">
            <input type="hidden" name="register" value="1">
            
            <div class="form-group">
                <label for="reg_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" id="reg_name" name="name" class="form-input" required>
                <p id="reg_name-error" class="error-message"></p>
            </div>
            
            <div class="form-group">
                <label for="reg_student_id" class="block text-sm font-medium text-gray-700">Student ID</label>
                <input type="text" id="reg_student_id" name="student_id" class="form-input" required>
                <p id="reg_student_id-error" class="error-message"></p>
            </div>

            <div class="form-group">
                <label for="reg_email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" id="reg_email" name="email" class="form-input" required>
                <p id="reg_email-error" class="error-message"></p>
            </div>
            
            <div class="form-group">
                <label for="reg_password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="reg_password" name="password" class="form-input" required>
                <p id="reg_password-error" class="error-message"></p>
            </div>
            
            <button type="submit" class="w-full bg-green-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-green-700 transition duration-150">
                Register
            </button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>