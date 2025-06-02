<?php
session_start();

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
}

function requireRole($role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header('Location: /unauthorized');
        exit;
    }
}

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

function currentUserName() {
    return $_SESSION['user_name'] ?? 'Usuário';
}

function currentUserRole() {
    return $_SESSION['role'] ?? null;
}
