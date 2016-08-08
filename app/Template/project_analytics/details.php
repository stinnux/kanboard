
<?php foreach ($values as $value): ?>
        <tr>
            <td><?= $this->url->link($this->text->e($value['user_fullname'] ?: $value['username']), 'UserViewController', 'show', array('user_id' => $value['user_id'])) ?></td>
            <td><?= $this->text->markdown($value['comment']) ?></td>
            <td><?= $this->dt->date($value['start']) ?></td>
            <td class='right'><?= n($value['time_spent']).' '.t('hours') ?></td>
        </tr>
  <?php endforeach ?>
