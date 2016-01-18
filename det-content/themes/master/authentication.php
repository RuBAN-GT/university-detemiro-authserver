<?php
    detemiro::theme()->incFile('header.php');
?>
<div id="page-auth" class="container">
    <div id="panel-auth" class="panel panel-default">
        <div class="panel-heading"><i class="glyphicon glyphicon-lock"></i> Аутентификация</div>
        <div class="panel-body">
            <form method="POST">
                <?php $det_form->printInputList(); ?>

                <?php detemiro::actions()->makeZone('auth.public.main-form'); ?>

                <button class="btn btn-lg btn-primary btn-block" type="submit">Войти</button>
            </form>
        </div>
    </div>

    <?php detemiro::actions()->makeZone('theme.messages'); ?>
</div>
<?php detemiro::theme()->incFile('footer.php'); ?>