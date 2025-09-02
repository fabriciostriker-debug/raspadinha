<?php
session_start();
require 'includes/db.php';

$userId = $_SESSION['usuario_id'];

$stmt = $conn->prepare("SELECT balance, bonus_balance FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Calculate total balance (regular + bonus)
$bonus_balance = isset($user['bonus_balance']) ? $user['bonus_balance'] : 0;
$total_balance = $user['balance'] + $bonus_balance;

echo json_encode(['saldo' => number_format($total_balance, 2, ',', '.')]);
?>
