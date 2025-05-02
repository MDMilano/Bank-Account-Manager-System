<?php
    include_once 'config/settings-configuration.php';
    require_once 'dashboard/authentication/bank-account.php';
    $bank = new bankAccount();

    if (empty($_SESSION['auto_generated_account_number'])){
        do {
            $auto_generated_account_number = rand(100000000000, 999999999999);
            $stmt = $bank->runQuery("SELECT account_number FROM account_info WHERE account_number = :account_number");
            $stmt->execute(array(":account_number" => $auto_generated_account_number));
        } while($stmt->rowCount() > 0);
        
        $_SESSION['auto_generated_account_number'] = $auto_generated_account_number;        
    }else{
        $auto_generated_account_number = $_SESSION['auto_generated_account_number'];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banknginamo</title>
    <link rel="icon" type="image/png" href="src/images/icon.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="src/css/signin-signup.css">
</head>
<body>
    <div class="auth-container">
        <!-- Sign In Form -->
        <div class="form-container active" id="signInForm">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-logo">
                        <img src="src/images/icon.png" alt="Banknginamo Logo" height="40">
                    </div>
                    <h2 class="mb-0">Sign In</h2>
                    <p class="mb-0 opacity-75">Access your account</p>
                </div>
                <div class="auth-form">
                    <form action="dashboard/authentication/bank-account.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token?>">
                        
                        <div class="mb-3">
                            <label for="signin_account_number" class="form-label">Account Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                                <input type="number" class="form-control" id="signin_account_number" name="account_number" placeholder="Enter your account number" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="signin_pin" class="form-label">PIN</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control pin-input" id="signin_pin" name="pin" placeholder="4-digit PIN" maxlength="4" pattern="[0-9]{4}" required>
                            </div>
                            <div class="form-text">Enter your 4-digit PIN</div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="btn-sign-in" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                            </button>
                        </div>
                    </form>
                    
                    <div class="form-toggle">
                        <p>Don't have an account?</p>
                        <button type="button" class="form-toggle-btn" id="showSignUpBtn">Sign up</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sign Up Form -->
        <div class="form-container" id="signUpForm">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-logo">
                        <img src="src/images/icon.png" alt="Banknginamo Logo" height="40">
                    </div>
                    <h2 class="mb-0">Create Account</h2>
                    <p class="mb-0 opacity-75">Join Banknginamo today</p>
                </div>
                <div class="auth-form">
                    <form action="dashboard/authentication/bank-account.php" method="post" id="createAccountForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token?>">
                        
                        <div class="mb-3">
                            <label for="signup_account_number" class="form-label">Account Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                                <input type="number" class="form-control" id="signup_account_number" name="account_number" value="<?php echo $_SESSION['auto_generated_account_number']; ?>" readonly>
                            </div>
                            <div class="form-text">Your account number will be generated automatically</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="owner_name" class="form-label">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="owner_name" name="owner_name" placeholder="Enter your full name" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="pin" class="form-label">PIN</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control pin-input" id="pin" name="pin" placeholder="4-digit PIN" maxlength="4" pattern="[0-9]{4}" required>
                            </div>
                            <div class="form-text">Create a 4-digit PIN for your account</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_pin" class="form-label">Confirm PIN</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control pin-input" id="confirm_pin" placeholder="Confirm 4-digit PIN" maxlength="4" pattern="[0-9]{4}" required>
                            </div>
                            <div class="pin-feedback" id="pinFeedback"></div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="btn-sign-up" class="btn btn-primary btn-lg" id="signupButton" disabled>
                                <i class="bi bi-person-plus me-2"></i>Create Account
                            </button>
                        </div>
                    </form>
                    
                    <div class="form-toggle">
                        <p>Already have an account?</p>
                        <button type="button" class="form-toggle-btn" id="showSignInBtn">Sign in</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="src/js/signin-signup.js"></script>
</body>
</html>