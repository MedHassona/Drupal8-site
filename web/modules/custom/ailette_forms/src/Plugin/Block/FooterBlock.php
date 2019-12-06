<?php

namespace Drupal\ailette_forms\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * @Block(
 *   id = "my_block_example_block",
 *   admin_label = @Translation("ayaline block"),
 * )
 */
class FooterBlock extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {
        return Array ('#markup' => '<strong>this is my footer  block yaay</strong>');
    }

    /**
     * {@inheritdoc}
     */
    protected function blockAccess(AccountInterface $account) {
        return AccessResult::allowedIfHasPermission($account, 'access content');
    }

    /**
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state) {
        $config = $this->getConfiguration();

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state) {
        $this->configuration['my_block_settings'] = $form_state->getValue('my_block_settings');
    }

}