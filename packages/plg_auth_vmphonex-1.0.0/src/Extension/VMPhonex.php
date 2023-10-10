<?php

/**
 * Noting original copyright ownership prior to update to Joomla 4
 * package		plg_auth_email
 * copyright	Copyright (C) 2005 - 2011 Michael Richey. All rights reserved.
 * Copyright (C) 2023 Oleg Marychev
 * license		GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Authentication\VMPhonex\Extension;

use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @package Joomla\Plugin\Authentication\VMPhonex\Extension
 * @author Merrill Squiers
 * @author Oleg Marychev
 * @since  Joomla 4.0
 * @version 1.0.0
 */
final class VMPhonex extends CMSPlugin
{
    // NOTE: Cannot use subscriberinterface because Authentication.php explicitly requires 
    // onUserAuthenticate method as of Joomla 4.3.1



    use DatabaseAwareTrait;

    // The following properties are initialized by CMSPlugin::__construct()
    protected $db;
    protected $app;
    protected $autoloadLanguage = true;

    private $_paj;

    public function __construct(&$subject, $config = [])
    {
        parent::__construct($subject, $config);
        $paj = PluginHelper::getPlugin('authentication', 'joomla');
        $this->_paj = new \Joomla\Plugin\Authentication\Joomla\Extension\Joomla($subject, (array)$paj);
    }

    /**
     * This method should handle any authentication and report back to the subject
     */
    function onUserAuthenticate(&$credentials, $options, &$response)
    {
        $query = $this->db->getQuery(true);

        $username = $this->app->input->post->get('username', false, 'RAW');
//        $query->select('id, username, password')
//            ->from('#__users')
//            ->where('block = 0')
           // ->where('UPPER(email) = UPPER(' . $this->db->Quote($username) . ')');
        $query->select('u.id as id, username, password')
        ->from('#__users as u')
	        ->join("RIGHT", "#__virtuemart_userinfos AS fv ON u.id = fv.virtuemart_user_id and fv.phone_1=" . $this->db->Quote($username))
	        ->where('u.block = 0');
//	        ->join("RIGHT", "#__fields_values AS fv ON u.id = fv.item_id and fv.value=" . $this->db->Quote($username))
//	        ->join("RIGHT", "#__fields as f ON fv.field_id = f.id and f.name='phone'")

        $this->db->setQuery($query);
        $result = $this->db->loadObject();

        if ($result) {
            // why mess with re-creating authentication - just use the system.
            $credentials['username'] = $result->username;
            $this->_paj->setDatabase($this->db);
            $this->_paj->setApplication($this->app);
            $this->_paj->onUserAuthenticate($credentials, $options, $response);
        } else {
            $response->status = Authentication::STATUS_FAILURE;
            $response->error_message = Text::_('JGLOBAL_AUTH_INVALID_PASS');
        }
    }
}
