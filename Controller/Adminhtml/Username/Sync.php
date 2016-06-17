<?php
/**
 * Diglin GmbH - Switzerland
 *
 * @author      Sylvain Rayé <support at diglin.com>
 * @category    Diglin
 * @package     Diglin_
 * @copyright   Copyright (c) 2011-2016 Diglin (http://www.diglin.com)
 */

namespace Diglin\Username\Controller\Adminhtml\Username;

abstract class Sync extends \Magento\Backend\App\Action
{
    /**
     * Return file storage singleton
     *
     * @return \Diglin\Username\Model\Generate\Flag
     */
    protected function _getSyncSingleton()
    {
        return $this->_objectManager->get('Diglin\Username\Model\Generate\Flag');
    }

    /**
     * Return synchronize process status flag
     *
     * @return \Diglin\Username\Model\Generate\Flag
     */
    protected function _getSyncFlag()
    {
        return $this->_getSyncSingleton()->loadSelf();
    }
}
