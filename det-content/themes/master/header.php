<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=detemiro::router()->getTitle(); ?></title>
    <link rel="shortcut icon" href="<?=detemiro::theme()->getFileLink('images/favicon.ico'); ?>">
    <link rel="stylesheet" href="<?=detemiro::theme()->getFileLink('css/bootstrap.min.css'); ?>" />
    <link rel="stylesheet" href="<?=detemiro::theme()->getFileLink('css/style.css'); ?>" />
    <?php detemiro::actions()->makeZone('theme.header'); ?>
</head>
<body>
<div id="wrapper">
    <div class="content">
        <?php detemiro::actions()->makeZone('theme.content'); ?>