<div class="sidebar">
    <h2><?= t('Actions') ?></h2>

    <br><br>
    <ul>
        <li <?= $this->app->checkMenuSelection('ProjectAnalyticsController', 'billable') ?>>
            <?= $this->url->link(t('Billable hours'), 'ProjectAnalyticsController', 'billable') ?>
        </li>
        <li <?= $this->app->checkMenuSelection('ProjectAnalyticsController', 'spentvsbillable') ?>>
            <?= $this->url->link(t('Time spent vs Time billable'), 'ProjectAnalyticsController', 'spentvsbillable') ?>
        </li>
        <li <?= $this->app->checkMenuSelection('ProjectAnalyticsController', 'remaining') ?>>
            <?= $this->url->link(t('Remaining hours'), 'ProjectAnalyticsController', 'remaining') ?>
        </li>
        <li <?= $this->app->checkMenuSelection('ProjectAnalyticsController', 'stalled') ?>>
            <?= $this->url->link(t('Stalled tasks'), 'ProjectAnalyticsController', 'stalled') ?>
        </li>

        <?= $this->hook->render('template:project-user:sidebar') ?>
    </ul>
</div>
