
<?= $this->render('project_analytics/daterange',
      array('values' => $values,
            'function' => $function,
            'date_format' => $date_format,
            'date_formats' => $date_formats
      )
); ?>
