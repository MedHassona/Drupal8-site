<?php

namespace Drupal\ailette_forms\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class SubscribeForm extends FormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId(){
        return 'subscribe_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state){

        $values = array('ETUDIANT' => t('Etudiant'),
                        'EMPLOYEYR' => t('Employeyr'),
                        'RETRAITE' => t('Retraité'));
        $options = array(
            'unmois' => t('inscription pour un mois'),
            'sixmois' => t('inscription pour six mois'),
            'annee' => t('inscription pour une année'),
        );

        $form['nom'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Nom :'),
            '#required' => TRUE,
        ];

        $form['prenom'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Prenom :'),
            '#required' => TRUE,
        ];

        $form['phone'] = array(
            '#type' => 'tel',
            '#title' => $this->t('Numero de téléphone :'),
        );

        $form['fonctionnalite'] = array(
            '#title' => t('Fonctionnalité :'),
            '#type' => 'select',
            '#options' => $values,
        );

        $form['duree'] = array(
            '#type' => 'radios',
            '#title' => t('la durée d\'inscription :'),
            '#options' => $options,
            '#default_value' => $options['unmois'],
        );

        $form['date_debut'] = array(
            '#type' => 'date',
            '#title' => $this->t('Date de début :'),
            '#date_format' => 'd/m/Y',
            '#required' => TRUE
        );

        $form['enregestrer'] = array(
            '#type' => 'submit',
            '#value' => $this
                ->t('Save'),
        );

        return [
            '#prefix' => '<main><article class="form"><h2 class="title-nature">veuilez remplir le formulaire suivant</h2><div class="subscribe-form">',
            '#suffix' => '</div></article></main>',
            'form' => $form,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        parent::validateForm($form, $form_state);

        if (strlen($form_state->getValue('nom')) < 5) {
            // Set an error for the form element with a key of "title".
            $form_state->setErrorByName('nom', $this->t('le nom doit être plus de 5 littres !'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state){
        $messenger = \Drupal::messenger();
        //converting date_debut to int for inserting
        $newdate = date('Ymd', strtotime($form_state->getValue('date_debut')));
        //to get it back as a date
        //$date_debut = date('Y-m-d', strtotime($newdate));

        $subscribe_values = array(
            'nom' => $form_state->getValue('nom'),
            'prenom' => $form_state->getValue('prenom'),
            'phone' => $form_state->getValue('phone'),
            'fonctionnalite' => $form_state->getValue('fonctionnalite'),
            'duree' => $form_state->getValue('duree'),
            'debut_date' => $newdate,
        );

        $query = \Drupal::database();

        $query->insert('data_subscribe')
              ->fields($subscribe_values)
              ->execute();
        if(!is_null($query)){
            $messenger->addMessage('vous êtes enregistré ... !');
        }
        $form_state->setRedirect('<front>');
    }

}