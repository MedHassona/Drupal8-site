<?php

/**
 * Implements hook_views_data_alter().
 */
function evenements_views_data_alter(array &$data) {
  $data['node_field_data']['nodes_titles'] = array(
    'title' => t('Node titles'),
    'filter' => array(
      'title' => t('Node titles'),
      'help' => t('Specify a list of titles a node can have.'),
      'field' => 'date-debaut',
      'id' => 'ailette_agenda_date-filter'
    ),
  );
}
