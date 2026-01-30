<?php
$page_title = 'Verify Customer - Admin Portal';
$current_page = 'verification';

require_once 'admin_middleware.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_pin'])) {
    $pin = sanitize_input($_POST['pin'] ?? '');
    
    if (empty($pin) || strlen($pin) !== 6) {
        $message = 'Please enter a valid 6-digit PIN.';
        $message_type = 'error';
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, email FROM users WHERE verification_pin = ? AND role = 'customer'");
        $stmt->bind_param("s", $pin);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $customer = $result->fetch_assoc();
            $message = '<strong>Verification Successful!</strong><br>Customer: ' . htmlspecialchars($customer['full_name']) . ' (' . htmlspecialchars($customer['email']) . ')';
            $message_type = 'success';
        } else {
            $message = 'Invalid Verification PIN. Please ask the customer to check their profile.';
            $message_type = 'error';
        }
    }
}

require_once 'header.php';
require_once 'sidebar.php';
?>

<main class="admin-main">
    <div class="admin-container">
        <div class="page-header">
            <div>
                <h1>Customer Verification</h1>
                <p class="text-secondary">Verify customer identity for security and orders.</p>
            </div>
        </div>

        <div class="stats-grid" style="grid-template-columns: 1fr;">
            <div class="dashboard-card" style="max-width: 500px; margin: 0 auto;">
                <div class="card-header">
                    <h2>Enter PIN</h2>
                </div>
                <div class="card-content p-lg">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?> mb-lg">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="verification-form">
                        <div class="form-group text-center">
                            <label class="form-label mb-md">Customer Verification PIN</label>
                            <input type="text" 
                                   name="pin" 
                                   maxlength="6" 
                                   placeholder="000000" 
                                   class="form-input text-center" 
                                   style="font-size: 2.5rem; letter-spacing: 12px; height: 80px; font-weight: 700; color: var(--color-primary); border: 2px solid #eee; border-radius: var(--radius-md);"
                                   required
                                   autocomplete="off">
                        </div>
                        <div class="mt-xl">
                            <button type="submit" name="verify_pin" class="btn btn-primary btn-block btn-lg">
                                Verify Customer Identity
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>
