<?php
/**
 * Diglin GmbH - Switzerland
 *
 * @author      Sylvain Rayé <support at diglin.com>
 * @category    Diglin
 * @package     Diglin_
 * @copyright   Copyright (c) 2011-2016 Diglin (http://www.diglin.com)
 */

namespace Diglin\Username\Test\Helper;

/**
 * Class CustomerTest
 * @package Diglin\Username\Test\Helper
 */
class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private static $flattenedFixture = [
        'configData1' => [
            'input_validation'  => 'default',
            'custom_validation' => '',
            'min_length'        => '6',
            'max_length'        => '30'
        ],
        'configData2' => [
            'input_validation'  => 'default',
            'custom_validation' => '',
            'min_length'        => '3',
            'max_length'        => '50'
        ],
        'configData3' => [
            'input_validation'  => 'alpha',
            'custom_validation' => '',
            'min_length'        => '6',
            'max_length'        => '30'
        ],
        'configData4' => [
            'input_validation'  => 'numeric',
            'custom_validation' => '',
            'min_length'        => '6',
            'max_length'        => '30'
        ],
        'configData5' => [
            'input_validation'  => 'alphanumeric',
            'custom_validation' => '',
            'min_length'        => '6',
            'max_length'        => '30'
        ],
        'configData6' => [
            'input_validation'  => 'custom',
            'custom_validation' => '^[\d]{4}\-[\d]{4}\-[\w]{4}$',
            'min_length'        => '6',
            'max_length'        => '30'
        ],
    ];

    public function testGenerateCustomer()
    {
        foreach (self::$flattenedFixture as $config) {
            $inputValidation = $config['input_validation'];
            $minLength = $config['min_length'];
            $maxLength = $config['max_length'];
            $customValidation = $config['custom_validation'];

            switch ($inputValidation) {
                case 'alpha': // letters
                    $regex = '^[a-zA-Z]{%d,%d}$';
                    break;
                case 'numeric': // digits
                    $regex = '^[\d]{%d,%d}$';
                    break;
                case 'alphanumeric': // letters & digits
                    $regex = '^[\d\w]{%d,%d}$';
                    break;
                case 'custom':
                    $regex = $customValidation;
                    break;
                case 'default': // letters, digits and _- characters
                default:
                    $regex = '^[\w]{%d,%d}$';
                    break;
            }

            $regex = sprintf($regex, $minLength, $maxLength);

            // 1. Read the grammar.
            $grammar = new \Hoa\File\Read('hoa://Library/Regex/Grammar.pp');

            // 2. Load the compiler.
            $compiler = \Hoa\Compiler\Llk\Llk::load($grammar);

            // 3. Lex, parse and produce the AST.
            $ast = $compiler->parse($regex);

            $generator = new \Hoa\Regex\Visitor\Isotropic(new \Hoa\Math\Sampler\Random());
            $username = $generator->visit($ast);

            echo sprintf('Username %s with the following regex has been tested %s', $username, $regex) . PHP_EOL;

            $this->assertRegExp('@'.$regex.'@', $username, 'Username does not comply to the regex ' . $regex);
        }
    }
}
