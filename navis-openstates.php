<?php
/***
 * Plugin Name: Navis OpenStates
 * Description: WordPress plugin to embed legislator and bill information from the OpenStates API.
 * Version: 0.1
 * Author: Chris Amico
 * License: GPLv2
***/
/*
    Copyright 2011 National Public Radio, Inc. 

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Navis_OpenStates {
    
    function __construct() {
        // tinymce button to open modal
        
        // shortcode to render legislator
        
        // shortcode to render bill
        
        // include assets
        add_action( 'admin_print_scripts-post.php', 
            array( &$this, 'register_admin_scripts' )
        );
        add_action( 'admin_print_scripts-post-new.php', 
            array( &$this, 'register_admin_scripts' )
        );
        
        add_action( 
            'admin_print_styles-post.php', 
            array( &$this, 'add_admin_stylesheet' ) 
        );
        add_action( 
            'admin_print_styles-post-new.php', 
            array( &$this, 'add_admin_stylesheet' ) 
        );
    }
    
    function bill_shortcode() {
        
    }
    
    function legislator_shortcode() {
        
    }
    
    function register_admin_scripts() {
        $js = array(
            'underscore' => plugins_url('js/underscore-min.js', __FILE__),
            'backbone' => plugins_url('js/backbone-min.js', __FILE__),
            'backbone-modelbinding' => plugins_url('js/backbone.modelbinding.min.js', __FILE__),
            'openstates-legislators' => plugins_url('js/openstates/legislators.js', __FILE__),
            'openstates-bills' => plugins_url('js/openstates/bills.js', __FILE__)
        );

        wp_enqueue_script( 'underscore', $js['underscore']);
        wp_enqueue_script( 'backbone', $js['backbone'],
            array('underscore', 'jquery'));
        wp_enqueue_script( 'backbone-modelbinding', $js['backbone-modelbinding'],
            array('underscore', 'backbone', 'jquery'));
        wp_enqueue_script( 'openstates-legislators', $js['openstates-legislators'], 
            array('underscore', 'backbone', 'jquery', 'backbone-modelbinding'), '0.1');
        wp_enqueue_script( 'openstates-bills', $js['openstates-bills'], 
            array('underscore', 'backbone', 'jquery', 'backbone-modelbinding'), '0.1');        
    }
}

new Navis_OpenStates;

?>