<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= BASE_URL ?>">
    <meta name="csrf-token" content="<?= Csrf::token() ?>">
    <title><?= e($title ?? 'Luminara Library') ?></title>
    <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">
</head>
<body>
<?php $flash = getFlash(); if ($flash): ?>
    <div class="flash-message <?= e($flash['type']) ?>" style="display:none"><?= e($flash['message']) ?></div>
<?php endif; ?>
