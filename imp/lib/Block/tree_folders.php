<?php
/**
 * Copyright 2005-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @package Horde_Block
 */

$block_name = _("Menu Folder List");
$block_type = 'tree';

class Horde_Block_imp_tree_folders extends Horde_Block
{
    protected $_app = 'imp';

    protected function _buildTree(&$tree, $indent = 0, $parent = null)
    {
        try {
            new IMP_Application(array('init' => array('authentication' => 'throw')));
        } catch (Horde_Exception $e) {
            return;
        }

        /* Run filters now */
        if ($GLOBALS['prefs']->getValue('filter_on_sidebar')) {
            $imp_filter = new IMP_Filter();
            $imp_filter->filter('INBOX');
        }

        /* Cache some additional values. */
        $image_dir = $GLOBALS['registry']->getImageDir();

        $tree->addNode($parent . 'compose', $parent, _("New Message"),
                       $indent, false,
                       array('icon' => 'compose.png',
                             'icondir' => $image_dir,
                             'url' => IMP::composeLink(),
                             'target' => $GLOBALS['prefs']->getValue('compose_popup') ? 'horde_menu' : 'horde_main'));

        /* Add link to the search page. */
        $tree->addNode($parent . 'search', $parent, _("Search"),
                       $indent, false,
                       array('icon' => 'search.png',
                             'icondir' => $image_dir,
                             'url' => Horde::applicationUrl('search.php')));

        if ($_SESSION['imp']['protocol'] == 'pop') {
            return;
        }

        $name_url = Horde_Util::addParameter(Horde::applicationUrl('mailbox.php'), 'no_newmail_popup', 1);

        /* Initialize the IMP_Tree object. */
        $imaptree = IMP_Imap_Tree::singleton();
        $mask = IMP_Imap_Tree::NEXT_SHOWCLOSED;
        if ($GLOBALS['prefs']->getValue('subscribe')) {
            $mask |= IMP_Imap_Tree::NEXT_SHOWSUB;
        }

        $unseen = 0;
        $inbox = null;
        $tree_ob = $imaptree->build($mask, null, null, false);

        foreach ($tree_ob[0] as $val) {
            $label = $val['name'];
            if (!empty($val['unseen'])) {
                $unseen += $val['unseen'];
                $label = '<span dir="ltr"><strong>' . $label . '</strong> (' . $val['unseen'] . '/' . $val['msgs'] . ')</span>';
            }

            /* If this is the INBOX, save it to later rewrite our parent node
             * to include new mail notification. */
            if ($val['value'] == 'INBOX') {
                $inbox = $val;
            }

            $node_params = array(
                'icon' => $val['icon'],
                'icondir' => $val['icondir'],
                'iconopen' => $val['iconopen'],
                'url' => ($val['container']) ? null : Horde_Util::addParameter($name_url, 'mailbox', $val['value']),
            );
            $tree->addNode($parent . $val['value'],
                           ($val['level']) ? $parent . $val['parent'] : $parent,
                           $label, $indent + $val['level'], $imaptree->isOpenSidebar($val['value']), $node_params);
        }

        /* We want to rewrite the parent node of the INBOX to include new mail
         * notification. */
        if ($inbox) {
            $url = $GLOBALS['registry']->get('url', $parent);
            if (empty($url)) {
                if (($GLOBALS['registry']->get('status', $parent) == 'heading') ||
                    !$GLOBALS['registry']->get('webroot')) {
                    $url = null;
                } else {
                    $url = Horde::url($GLOBALS['registry']->getInitialPage($parent));
                }
            }

            $node_params = array('url' => $url,
                                 'icon' => $GLOBALS['registry']->get('icon', $parent),
                                 'icondir' => '');
            $menu_parent = $GLOBALS['registry']->get('menu_parent', $parent);
            $name = $GLOBALS['registry']->get('name', $parent);
            if ($unseen) {
                $node_params['icon'] = 'newmail.png';
                $node_params['icondir'] = $image_dir;
                $name = sprintf('<strong>%s</strong> (%s)', $name, $unseen);
            }
            $tree->addNode($parent, $menu_parent, $name, $indent - 1, $imaptree->isOpenSidebar($parent), $node_params);
        }
    }

}
