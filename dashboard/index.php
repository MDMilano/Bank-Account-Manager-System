<?php
include_once '../config/settings-configuration.php';
require_once 'authentication/bank-account.php';
$bank = new bankAccount();

if(!$bank->isSignedIn()){
    $bank->redirect('User must sign in first!', '../');
}else{
    $stmt = $bank->runQuery("SELECT * FROM account_info WHERE account_number = :account_number");
    $stmt->execute(array(":account_number" => $_SESSION['account_number']));
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $bank->runQuery("SELECT * FROM transactions WHERE account_number = :account_number OR recipient_account_number = :recipient_account_number ORDER BY transaction_date DESC");
    $stmt->execute(array(":account_number" => $_SESSION['account_number'], ":recipient_account_number" => $_SESSION['account_number']));
    $userTransaction = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banknginamo</title>
    <link rel="icon" type="image/png" href="../src/images/icon.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../src/css/styles.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar Overlay (Mobile) -->
        <div id="sidebarOverlay"></div>
        
        <!-- Sidebar Toggle Button -->
        <button class="toggle-sidebar" id="sidebarToggle">
            <i class="bi bi-chevron-left" id="toggleIcon"></i>
        </button>
        
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <img src="../src/images/icon.png" alt="Banknginamo Logo" height="30" class="me-2">
                <span class="sidebar-text">Banknginamo</span>
            </div>
            
            <ul class="sidebar-nav nav flex-column mt-3">
                <li class="nav-item">
                    <a class="nav-link active" href="#">
                        <i class="bi bi-speedometer2"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#depositModal">
                        <i class="bi bi-wallet"></i>
                        <span class="sidebar-text">Deposit</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                        <i class="bi bi-cash-coin"></i>
                        <span class="sidebar-text">Withdraw</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#transferModal">
                        <i class="bi bi-arrow-left-right"></i>
                        <span class="sidebar-text">Transfer</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#balanceModal">
                        <i class="bi bi-piggy-bank"></i>
                        <span class="sidebar-text">Balance</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#transactionHistoryModal">
                        <i class="bi bi-clock-history"></i>
                        <span class="sidebar-text">History</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#viewProfileModal">
                        <i class="bi bi-person-circle"></i>
                        <span class="sidebar-text">Profile</span>
                    </a>
                </li>
            </ul>
            
            <div class="user-info mt-auto">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-white rounded-circle text-primary d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                        <?php echo substr($userData['owner_name'], 0, 1); ?>
                    </div>
                    <div class="user-details">
                        <span class="d-block text-white sidebar-text"><?php echo $userData['owner_name']; ?></span>
                    </div>
                </div>
                <a href="authentication/bank-account.php?sign-out" class="btn btn-light btn-sm w-100">
                    <i class="bi bi-box-arrow-right me-1"></i>
                    <span class="sidebar-text">Sign Out</span>
                </a>
            </div>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <div class="container-fluid py-4 px-4">
                <!-- Welcome Banner -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm dashboard-card">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-0">Welcome, <?php echo explode(' ', $userData['owner_name'])[0]; ?>!</h5>
                                <p class="text-muted">Here's your account summary</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Balance Card (New Design) -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="balance-card">
                            <div class="balance-title">Current Balance</div>
                            <div class="balance-amount">₱ <?php echo number_format($userData['balance'], 2); ?></div>
                            <div class="balance-actions">
                                <button class="balance-action-btn" data-bs-toggle="modal" data-bs-target="#depositModal">
                                    <i class="bi bi-plus-circle"></i>
                                    Deposit
                                </button>
                                <button class="balance-action-btn" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                                    <i class="bi bi-dash-circle"></i>
                                    Withdraw
                                </button>
                                <button class="balance-action-btn" data-bs-toggle="modal" data-bs-target="#transferModal">
                                    <i class="bi bi-arrow-left-right"></i>
                                    Transfer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="fw-bold mb-3">Quick Actions</h5>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="quick-action-card">
                            <div class="quick-action-icon">
                                <i class="bi bi-wallet"></i>
                            </div>
                            <h5>Deposit</h5>
                            <p class="text-muted">Add funds to your account</p>
                            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#depositModal">
                                Deposit Now
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="quick-action-card">
                            <div class="quick-action-icon">
                                <i class="bi bi-cash-coin"></i>
                            </div>
                            <h5>Withdraw</h5>
                            <p class="text-muted">Withdraw funds from your account</p>
                            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                                Withdraw Now
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="quick-action-card">
                            <div class="quick-action-icon">
                                <i class="bi bi-arrow-left-right"></i>
                            </div>
                            <h5>Transfer</h5>
                            <p class="text-muted">Send money to another account</p>
                            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#transferModal">
                                Transfer Now
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Account Overview -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="fw-bold mb-3">Account Overview</h5>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card dashboard-card h-100">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4">Account Information</h5>
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center border-0">
                                        <div>
                                            <i class="bi bi-credit-card text-primary me-2"></i>
                                            <span class="fw-medium">Account Number</span>
                                        </div>
                                        <span class="badge bg-light text-dark fs-6 fw-normal"><?php echo $bank->hideAccountNumber($userData['account_number'], 6, 6); ?></span>
                                    </div>
                                    
                                    <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center border-0">
                                        <div>
                                            <i class="bi bi-person text-primary me-2"></i>
                                            <span class="fw-medium">Account Holder</span>
                                        </div>
                                        <span class="text-dark"><?php echo $userData['owner_name']; ?></span>
                                    </div>
                                    
                                    <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center border-0">
                                        <div>
                                            <i class="bi bi-calendar-check text-primary me-2"></i>
                                            <span class="fw-medium">Member Since</span>
                                        </div>
                                        <span class="text-dark">
                                            <?php echo date("F d, Y", strtotime($userData['created_at'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-3 text-center">
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewProfileModal">
                                        <i class="bi bi-person-circle me-1"></i>View Full Profile
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card dashboard-card h-100">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="card-title mb-0">Recent Activity</h5>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#transactionHistoryModal">
                                        View All
                                    </button>
                                </div>
                                
                                <div class="transaction-list">
                                    <?php if(empty($userTransaction)): ?>
                                        <div class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-clock-history fs-1"></i>
                                                <p class="mt-3">No recent transactions</p>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach(array_slice($userTransaction, 0, 3) as $transaction): ?>
                                            <div class="transaction-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="transaction-icon transaction-<?php echo $transaction['transaction_type']; ?>">
                                                        <?php if($transaction['transaction_type'] == 'deposit'): ?>
                                                            <i class="bi bi-wallet"></i>
                                                        <?php elseif($transaction['transaction_type'] == 'withdrawal'): ?>
                                                            <i class="bi bi-cash-coin"></i>
                                                        <?php else: ?>
                                                            <i class="bi bi-arrow-left-right"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <div class="transaction-date"><?php echo date('M d, Y - h:i A', strtotime($transaction['transaction_date'])); ?></div>
                                                    </div>
                                                </div>
                                                <div class="transaction-amount <?php 
                                                    if ($transaction['transaction_type'] == 'deposit') {
                                                        echo 'deposit';
                                                    } elseif ($transaction['transaction_type'] == 'transfer' && $transaction['recipient_account_number'] == $_SESSION['account_number']) {
                                                        echo 'deposit'; // Use deposit class for incoming transfers (green color)
                                                    } else {
                                                        echo $transaction['transaction_type']; // Use original class for withdrawals and outgoing transfers
                                                    }
                                                ?>">
                                                    <?php 
                                                    if ($transaction['transaction_type'] == 'deposit') {
                                                        echo '+';
                                                    } elseif ($transaction['transaction_type'] == 'transfer' && $transaction['recipient_account_number'] == $_SESSION['account_number']) {
                                                        echo '+'; // Incoming transfer (I'm the recipient)
                                                    } else {
                                                        echo '-'; // Withdrawal or outgoing transfer
                                                    }
                                                    ?>₱ <?php echo number_format($transaction['amount'], 2); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <footer class="mt-auto py-3 text-center text-muted">
                    <small>&copy; <?php echo date('Y'); ?> Banknginamo. All rights reserved.</small>
                </footer>
            </div>
        </div>
    </div>

    <!-- Deposit Modal -->
    <div class="modal fade" id="depositModal" tabindex="-1" aria-labelledby="depositModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="authentication/bank-account.php" method="post">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="depositModalLabel">
                            <i class="bi bi-wallet me-2"></i>Deposit Funds
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label for="depositAmount" class="form-label fw-medium">Amount to Deposit</label>
                            <div class="input-group">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token?>">
                                <span class="input-group-text">₱</span>
                                <input type="number" name="depositAmount" class="form-control form-control-lg" id="depositAmount" placeholder="0.00" min="0" step="0.01" required>
                            </div>
                            <div class="form-text mt-2">Enter the amount you wish to deposit into your account.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <input type="submit" name="btn-deposit" class="btn btn-primary" value="Deposit">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Withdraw Modal -->
    <div class="modal fade" id="withdrawModal" tabindex="-1" aria-labelledby="withdrawModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="authentication/bank-account.php" method="post">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="withdrawModalLabel">
                            <i class="bi bi-cash-coin me-2"></i>Withdraw Funds
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label for="withdrawAmount" class="form-label fw-medium">Amount to Withdraw</label>
                            <div class="input-group">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token?>">
                                <span class="input-group-text">₱</span>
                                <input type="number" name="withdrawAmount" class="form-control form-control-lg" id="withdrawAmount" placeholder="0.00" min="0" step="0.01" required>
                            </div>
                            <div class="form-text mt-2">Current balance: ₱ <?php echo number_format($userData['balance'], 2); ?></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <input type="submit" name="btn-withdraw" class="btn btn-primary" value="Withdraw">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Transfer Modal -->
    <div class="modal fade" id="transferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="authentication/bank-account.php" method="post">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="transferModalLabel">
                            <i class="bi bi-arrow-left-right me-2"></i>Transfer Funds
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token?>">
                            <label for="accountNumber" class="form-label fw-medium">Recipient Account Number</label>
                            <input type="text" name="accountNumber" class="form-control" id="accountNumber" placeholder="Enter recipient account number" required>
                        </div>
                        <div class="mb-3">
                            <label for="transferAmount" class="form-label fw-medium">Amount to Transfer</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" name="transferAmount" class="form-control" id="transferAmount" placeholder="0.00" min="0" step="0.01" required>
                            </div>
                            <div class="form-text mt-2">Available balance: ₱ <?php echo number_format($userData['balance'], 2); ?></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <input type="submit" name="btn-transfer" class="btn btn-primary" value="Transfer">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Balance Modal -->
    <div class="modal fade" id="balanceModal" tabindex="-1" aria-labelledby="balanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="balanceModalLabel">
                        <i class="bi bi-piggy-bank me-2"></i>Account Balance
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="py-4">
                        <h6 class="text-muted mb-3">Your Current Balance</h6>
                        <h1 class="display-4 fw-bold text-primary mb-0">₱ <?php echo number_format($userData['balance'], 2); ?></h1>
                    </div>
                    <div class="row mt-4 g-3">
                        <div class="col-6">
                            <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#depositModal" data-bs-dismiss="modal">
                                <i class="bi bi-wallet me-1"></i>Deposit
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#withdrawModal" data-bs-dismiss="modal">
                                <i class="bi bi-cash-coin me-1"></i>Withdraw
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History Modal -->
    <div class="modal fade" id="transactionHistoryModal" tabindex="-1" aria-labelledby="transactionHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="transactionHistoryModalLabel">
                        <i class="bi bi-clock-history me-2"></i>Transaction History
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <select class="form-select" id="transactionTypeFilter">
                                    <option value="all">All Transactions</option>
                                    <option value="deposit">Deposits</option>
                                    <option value="withdrawal">Withdrawals</option>
                                    <option value="transfer">Transfers</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="date" class="form-control" id="startDate" placeholder="Start Date">
                            </div>
                            <div class="col-md-4">
                                <input type="date" class="form-control" id="endDate" placeholder="End Date">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                        <div id="filterStatus" class="text-muted small">Showing: All transactions</div>
                        <button id="clearFilters" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Clear Filters
                        </button>
                    </div>
                    
                    <!-- No transactions message (hidden by default) -->
                    <div id="noTransactionsMessage" class="text-center py-4" style="display: none;">
                        <div class="text-muted">
                            <i class="bi bi-search fs-1"></i>
                            <p class="mt-3">No transactions match your filters</p>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover transaction-table">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Transaction Type</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($userTransaction)): ?>
                                    <tr id="noTransactionsInitial">
                                        <td colspan="3" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-clock-history fs-1"></i>
                                                <p class="mt-3">No transaction history</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($userTransaction as $transaction): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y - h:i A', strtotime($transaction['transaction_date'])); ?></td>
                                        <td>
                                            <span class="transaction-badge <?php echo $transaction['transaction_type']; ?>">
                                                <?php 
                                                    if($transaction['transaction_type'] == 'deposit') echo 'Deposit';
                                                    elseif($transaction['transaction_type'] == 'withdrawal') echo 'Withdrawal';
                                                    elseif($transaction['transaction_type'] == 'transfer') {
                                                        if($transaction['recipient_account_number'] == $_SESSION['account_number']) {
                                                            echo 'Transfer In';
                                                        } else {
                                                            echo 'Transfer Out';
                                                        }
                                                    } else {
                                                        echo 'Transfer';
                                                    }
                                                ?>
                                            </span>
                                        </td>
                                        <td class="<?php 
                                            if ($transaction['transaction_type'] == 'deposit') {
                                                echo 'text-success';
                                            } elseif ($transaction['transaction_type'] == 'transfer' && $transaction['recipient_account_number'] == $_SESSION['account_number']) {
                                                echo 'text-success';
                                            } else {
                                                echo 'text-danger';
                                            }
                                        ?>">
                                            <?php 
                                            if ($transaction['transaction_type'] == 'deposit') {
                                                echo '+';
                                            } elseif ($transaction['transaction_type'] == 'transfer' && $transaction['recipient_account_number'] == $_SESSION['account_number']) {
                                                echo '+';
                                            } else {
                                                echo '-';
                                            }
                                            ?>₱ <?php echo number_format($transaction['amount'], 2); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Profile -->
    <div class="modal fade" id="viewProfileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="profileModalLabel">
                        <i class="bi bi-person-circle me-2"></i>Account Profile
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <!-- Profile Header with Avatar -->
                    <div class="text-center bg-light p-4 border-bottom">
                        <div class="profile-avatar rounded-circle bg-primary d-flex align-items-center justify-content-center text-white mb-3">
                            <?php echo substr($userData['owner_name'], 0, 1); ?>
                        </div>
                        <h4 class="mb-1"><?php echo $userData['owner_name']; ?></h4>
                        <span class="badge bg-primary">Account Holder</span>
                    </div>
                    
                    <!-- Account Details -->
                    <div class="p-4">
                        <h6 class="text-uppercase text-muted mb-3 small fw-bold">Account Information</h6>
                        
                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-credit-card text-primary me-2"></i>
                                    <span class="fw-medium">Account Number</span>
                                </div>
                                <span class="badge bg-light text-dark fs-6 fw-normal"><?php echo $bank->hideAccountNumber($userData['account_number'], 6, 6); ?></span>
                            </div>
                            
                            <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-person text-primary me-2"></i>
                                    <span class="fw-medium">Username</span>
                                </div>
                                <span class="text-dark"><?php echo $userData['owner_name']; ?></span>
                            </div>
                            
                            <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-calendar-check text-primary me-2"></i>
                                    <span class="fw-medium">Member Since</span>
                                </div>
                                <span class="text-dark">
                                    <?php 
                                        echo date("F d, Y", strtotime($userData['created_at'])); 
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editModal">
                        <i class="bi bi-pencil-square me-1"></i>Edit Profile
                    </button>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="deleteAccountForm" action="authentication/bank-account.php" method="POST">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteAccountModalLabel">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Delete Account
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="text-center mb-4">
                            <div class="warning-icon bg-danger-subtle text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-exclamation-triangle-fill" style="font-size: 2.5rem;"></i>
                            </div>
                            <h4 class="fw-bold text-danger">Delete Your Account?</h4>
                            <p class="text-muted">This action cannot be undone.</p>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Warning:</strong> Deleting your account will permanently remove all your data, including transaction history and account information.
                        </div>
                        
                        <div class="mb-4">
                            <label for="deleteConfirmPin" class="form-label fw-medium">Enter your PIN to confirm</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control pin-input" name="deleteConfirmPin" id="deleteConfirmPin" placeholder="4-digit PIN" maxlength="4" pattern="[0-9]{4}" required>
                            </div>
                            <div class="form-text">Please enter your 4-digit PIN to confirm account deletion</div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmDeleteCheck" required>
                                <label class="form-check-label" for="confirmDeleteCheck">
                                    I understand that this action is permanent and cannot be undone
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                        <button type="submit" name="btn-delete-account" class="btn btn-danger" id="confirmDeleteBtn" disabled>
                            <i class="bi bi-trash me-1"></i>Delete Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="authentication/bank-account.php" method="post">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="editModalLabel">
                            <i class="bi bi-pencil-square me-1"></i>Edit Profile
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token?>">
                            <label for="username" class="form-label fw-medium">Username</label>
                            <input type="text" name="username" class="form-control" id="username" placeholder="Enter username" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <input type="submit" name="btn-edit-username" class="btn btn-primary" value="Edit">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="../src/js/scripts.js"></script>
</body>
</html>