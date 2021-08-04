<?php
/**
 * @file
 * Contains \Drupal\rsvplist\Form\RSVPForm
 */

namespace Drupal\rsvplist\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface; 

/**
 * Provides an RSVP Email Form
 */

class RSVPForm extends FormBase
{
    /**
     * (@inheritdoc)
     */
    public function getFormId() 
    {
        return 'rsvplist_email_form';
    }
    
    /**
     * 
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
    
        $node = \Drupal::routeMatch()->getParameter('node');
        // You can get nid and anything else you need from the node object.
        $nid = $node->id();
        
        $form['email'] = array(
            '#title' => t('Email Address'),
            '#type' => 'textfield',
            '#size' => 25,
            '#description' => t("We'll send updates to the email address your provide."),
            '#required' => true,
        );
        $form['submit'] = array(
            '#type' => 'submit',
            '#value' => t('RSVP'),
        );
        $form['nid'] = array(
            '#type' => 'hidden',
            '#value' => $nid,
        );
        
        return $form;
    }

    /**
     * Undocumented function
     *
     * @param  array              $form
     * @param  FormStateInterface $form_state
     * @return void
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $value = $form_state->getValue('email');
        if ($value == !\Drupal::service('email.validator')->isValid($value)) {
            $form_state->setErrorByName('email', t('The email address %mail is not valid.', array('%mail'=> $value)));

            return;
        }

        $node = \Drupal::routeMatch()->getParameter('node');
        
        // Check if email already is set for this node
        $select = Database::getConnection()->select('rsvplist', 'r');
        $select->fields('r', array('nid'));
        $select->condition('nid', $node->id);
        $select->condition('mail', $value);
        $results = $select->execute();

        if(!empty($results->fetchCol())) {
            //we found a row with this nid and email.
            $form_state->setErrorByName(
                'email', t('The address %mail is already subscribed to this list', array('%mail' => $value ))
            );
        }

    }
    /**
     * Undocumented function
     *
     * @param  array              $form
     * @param  FormStateInterface $form_state
     * @return void
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
        \Drupal::database()->insert('rsvplist')
            ->fields(
                array(
                'mail' => $form_state->getValue('email'),
                'nid' => $form_state->getValue('nid'),
                'uid' => $user->id(),
                'created' => time(),
                )
            )->execute();

        \Drupal::messenger()->addMessage('Email saved !');
    }
}