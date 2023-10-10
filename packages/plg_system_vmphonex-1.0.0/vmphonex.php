<?php

/**
 * Original author's header:
 * version		$Id: email.php 20196 2011-03-04 02:40:25Z mrichey $
 * package		plg_auth_email
 * copyright	Copyright (C) 2005 - 2011 Michael Richey. All rights reserved.
 * Copyright (C) 2023 Oleg Marychev
 * license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

/**
 * @package plgSystemVMPhonex
 * @author Merrill Squiers / Oleg Marychev
 * @since  Joomla 4.0
 * @version 1.0.0
 */

class plgSystemVMPhonex extends CMSPlugin implements SubscriberInterface
{
    // The following properties are initialized by CMSPlugin::__construct()
    protected $db;
    protected $app;
    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array { 
        return [
            'onAfterRoute' => 'handleResetConfirm',
        ];
    }

	function handleResetConfirm()
	{
            $app = $this->app;
            if($app->getName() === 'administrator') return;
            $component = $app->input->getCmd('option');
            if($component != 'com_users') return;
            $task = $app->input->getCmd('task');
            if($task != 'reset.confirm') return;
            
            // ok, at this point we know that the form has been submitted.
            $jform = $app->input->get('jform',array(),'array');
            if(count($jform) && preg_match('/@/',$jform['username'])) {
                $db = $this->db;
                $query = $db->getQuery(true);
//                $query->select('username')
//                    ->from('#__users')
//                    ->where('UPPER(email) = UPPER('.$db->quote($jform['username']).')')
//                    ->where('block = 0');
	            $query->select('u.id as id, username, password')
		            ->from('#__users as u')
		            ->join("RIGHT", "#__virtuemart_userinfos AS fv ON u.id = fv.virtuemart_user_id and fv.phone1=" . $db->quote($jform['username']))
		            ->where('u.block = 0');
//		            ->join("RIGHT", "#__fields_values AS fv ON u.id = fv.item_id and fv.value=" . $db->quote($jform['username']))
//		            ->join("RIGHT", "#__fields as f ON fv.field_id = f.id and f.name='phone'")
                $db->setQuery($query);
                $username = $db->loadResult();
                if( $username !== null ) {
                    $jform['username']=$username;
                    $app->input->set('jform',$jform);
                }
            }
        }
}
