<?php

namespace Drupal\ailette_forms\Controller;

Use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;

class FormsController extends ControllerBase {

    public function subscribe() {

        return [
            '#prefix' => '<article class="form"><div class="subscribe-form">',
            '#suffix' => '</div></article>',
            'form' => \Drupal::formBuilder()->getForm('Drupal\ailette_forms\Form\SubscribeForm'),
        ];
    }
}