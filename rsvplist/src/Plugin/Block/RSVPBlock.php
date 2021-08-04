<?php

namespace Drupal\rsvplist\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\node\NodeInterface;

/**
 * Provides an 'RSVP' List Block.
 *
 * @Block(
 *  id = "rsvp_block",
 *  admin_label = @Translation("RSVP Block"),
 * )
 */
class RSVPBlock extends BlockBase {

    /**
     * {@inheritDoc}
     */
    public function build() {
        return \Drupal::formBuilder()->getForm('Drupal\rsvplist\Form\RSVPForm');
    }

    /**
     * {@inheritDoc}
     */
    public function blockAccess(AccountInterface $account) {
        /** @var \Drupal\node\Entity\Node $node */
        $node = \Drupal::routeMatch()->getParameter('node');

        /** @var \Drupal\rsvplist\EnablerService $enabler */
        $enabler = \Drupal::service('rsvplist.enabler');

        if ($node instanceof NodeInterface) {
            $nid = $node->id();
            if (is_numeric($nid)) {
                if ($enabler->isEnabled($node)) {
                    return AccessResult::allowedIfHasPermission($account, 'view rsvplist');
                }
            }
        }

        return AccessResult::forbidden();
    }

}