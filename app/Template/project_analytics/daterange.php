<form method="post" class="form-inline" action="<?= $this->url->href('ProjectAnalyticsController', $function) ?>" autocomplete="off">

    <?= $this->form->csrf() ?>

    <div class="form-inline-group">
        <?= $this->form->label(t('Start Date'), 'from') ?>
        <?= $this->form->text('from', $values, array(), array('required', 'placeholder="'.$this->text->in($date_format, $date_formats).'"'), 'form-date') ?>
    </div>

    <div class="form-inline-group">
        <?= $this->form->label(t('End Date'), 'to') ?>
        <?= $this->form->text('to', $values, array(), array('required', 'placeholder="'.$this->text->in($date_format, $date_formats).'"'), 'form-date') ?>
    </div>

    <div class="form-inline-group">
        <button type="submit" class="btn btn-blue"><?= t('Execute') ?></button>
    </div>
</form>
