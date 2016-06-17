<?php
/**
 * Diglin GmbH - Switzerland
 *
 * @author      Sylvain Rayé <support at diglin.com>
 * @category    Diglin
 * @package     Diglin_Username
 * @copyright   Copyright (c) 2011-2016 Diglin (http://www.diglin.com)
 */

namespace Diglin\Username\Model\Config\Source;

class Inputvalidation implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @deprecated
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value'=>'default', 'label'=> __('Default (letters, digits and _- characters)')],
            ['value'=>'alphanumeric', 'label'=> __('Letters and digits')],
            ['value'=>'alpha', 'label'=> __('Letters only')],
            ['value'=>'numeric', 'label'=> __('Digits only')],
            ['value'=>'custom', 'label'=> __('Custom (PCRE Regex)')]
        ];
    }
}