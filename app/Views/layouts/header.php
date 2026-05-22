<!DOCTYPE html>
<?php $initialTheme = (($_COOKIE['theme'] ?? 'dark') === 'light') ? 'light' : 'dark'; ?>
<html lang="en" data-theme="<?= e($initialTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= BASE_URL ?>">
    <meta name="csrf-token" content="<?= Csrf::token() ?>">
    <title><?= e($title ?? 'Luminara Library') ?></title>
    <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
    <link rel="icon" type="image/png" href="<?= url('uploads/images/logo.png') ?>">
</head>
<body data-user-role="<?= e(Auth::user()['role'] ?? 'guest') ?>" data-authenticated="<?= Auth::check() ? '1' : '0' ?>">
<?php $flash = getFlash(); if ($flash): ?>
    <div class="flash-message <?= e($flash['type']) ?>" style="display:none"><?= e($flash['message']) ?></div>
<?php endif; ?>
