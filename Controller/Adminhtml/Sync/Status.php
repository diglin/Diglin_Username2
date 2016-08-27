<?php
/**
 * Diglin GmbH - Switzerland
 *
 * @author      Sylvain Rayé <support at diglin.com>
 * @category    Diglin
 * @package     Diglin_
 * @copyright   Copyright (c) 2011-2016 Diglin (http://www.diglin.com)
 */

namespace Diglin\Username\Controller\Adminhtml\Sync;


use Diglin\Username\Controller\Adminhtml\Sync;
use Diglin\Username\Model\Generate\Flag;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Json\Helper\Data;

/**
 * Class Status
 * @package Diglin\Username\Controller\Adminhtml\Username\Sync
 */
class Status extends Sync
{
    /**
     * @var Data
     */
    private $jsonHelper;

    /**
     * Status constructor.
     */
    public function __construct(
        Context $context,
        Data $jsonHelper
    ) {
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $flag = $this->_getSyncFlag();
        if ($flag) {
            $state = $flag->getState();
            $flagData = $flag->getFlagData();

            switch ($state) {
                case Flag::STATE_RUNNING:
                    if ($flagData['total_items'] > 0) {
                        $percent = (int)($flagData['total_items_done'] * 100 / $flagData['total_items']) . '%';
                        $result['message'] = __('Generating username: %s done.', $percent);
                    } else {
                        $result ['message'] = __('Generating...');
                    }
                    break;
                case Flag::STATE_FINISHED:
                    $result ['message'] = __('Generation finished');

                    if ($flag->getHasErrors()) {
                        $result ['message'] .= __('Errors occurred while running. Please, check the log if enabled.');
                        $result ['has_errors'] = true;
                    }
                    $state = Flag::STATE_NOTIFIED;
                    break;
                case Flag::STATE_NOTIFIED:
                    break;
                default:
                    $state = Flag::STATE_INACTIVE;
                    break;
            }
        } else {
            $state = Flag::STATE_INACTIVE;
        }
        $result['state'] = $state;

        $this->getResponse()->setBody($this->jsonHelper->jsonEncode($result));
    }
}