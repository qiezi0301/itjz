<?php
/**
 * Smarty Internal Plugin Compile Section
 *
 * Compiles the {section} {sectionelse} {/section} tags
 *
 * @package Smarty
 * @subpackage Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Section Class
 * 
 * @package Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Section extends Smarty_Internal_CompileBase {

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $required_attributes = array('name', 'loop');
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $shorttag_order = array('name', 'loop');
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = array('start', 'step', 'max', 'show');

    /**
     * Compiles code for the {section} tag
     *
     * @param array  $args     array with attributes from parser
     * @param object $compiler compiler object
     * @return string compiled code
     */
    public function compile($args, $compiler)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        $this->openTag($compiler, 'section', array('section', $compiler->nocach