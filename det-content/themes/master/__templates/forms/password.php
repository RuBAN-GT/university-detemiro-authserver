<div class="form-group<?=$det_class;?><?=($det_ignore) ? ' disabled' : '';?>">
    <?php if($det_title): ?>
        <label for="form-<?=$det_name;?>" class="control-label"><?=$det_title;?></label>
    <?php endif;?>

    <input
        type="password"
        id="form-<?=$det_name;?>"
        class="form-control"
        name="<?=$det_name;?>"
        placeholder="<?=$det_place;?>"
        value=""
        <?=($det_ignore) ? 'disabled' : '';?>
        <?=($det_require) ? 'required' : '';?>
        />
</div>