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
        add_shortcode('legislator', array(&$this, 'legislator_shortcode'));
        
        // shortcode to render bill
        add_shortcode('bill', array(&$this, 'bill_shortcode'));
        
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
        
        // settings
        add_action( 'admin_menu', array(&$this, 'add_options_page'));
        
        add_action( 'admin_init', array(&$this, 'settings_init'));
        
    }
    
    function fetch($url) {
        $response = wp_remote_retrieve_body( wp_remote_get($url) );
        if (empty($response)) {
            return false;
        }
        return json_decode($response);
    }
    
    function get_alignment($align) {
        $classnames = array(
            'left'=>'alignleft',
            'right'=>'alignright'
        );
        if (array_key_exists($align, $classnames)) {
            $classname = $classnames[$align];
        } else {
            $classname = 'alignleft';
        }
        return $classname;
    }
    
    function bill_shortcode() {
        
    }
    
    function legislator_shortcode($atts) {
        extract(shortcode_atts(array(
            'leg_id' => null,
            'align' => 'left'
        ), $atts));
        $apikey = get_option('sunlight_apikey');
        //if (!$leg_id) return;
        //if (!$apikey) return;
        
        $qs = http_build_query(array( 'apikey' => $apikey ));
        $url = "http://openstates.org/api/v1/legislators/$leg_id/?" . $qs;
        $data = $this->fetch($url);
        
        $classname = $this->get_alignment($align);
        $displayname = $data->full_name . " ({$data->party[0]})";
        $committees = $this->get_committees($data);
        
        $html  = "<div class=\"openstates-module legislator $classname\">";
        $html .=	"<h2 class=\"module-title\">Legislator Info</h2>";
        $html .=	"<div class=\"box-wrapper\">";
        $html .=		"<h3 class=\"name\">$displayname</h3>";
        $html .=		"<h4 class=\"district\">District {$data->district}</h4>";
        if ($committees) {
            $html .=		"<h5 class=\"info-hed\">Committees</h5>";
            $html .=		"<p>". implode(', ', $committees) . "</p>";
        }
        $html .=		"<p class=\"source\">Source: <a href=\"http://openstates.org/\">Open States</a></p>";
        $html .=	"</div>";
        $html .= "</div>";
        
        return $html;
    }
    
    function get_committees($leg_data) {
        $committees = array();
        foreach((array)$leg_data->roles as $role) {
            if ($role->type == 'committee member') {
                $committees[] = $role->committee;
            }
        }
        return $committees;
    }
    
    function add_admin_stylesheet() {}
    
    function register_admin_scripts($atts) {
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
    
    function add_options_page() {
        add_options_page('OpenStates', 'OpenStates', 'manage_options', 
                        'openstates', array(&$this, 'render_options_page'));
    }
    
    function render_options_page() { ?>
        <h2>OpenStates Options</h2>
        <form action="options.php" method="post">
            
            <?php settings_fields('openstates'); ?>
            <?php do_settings_sections('openstates'); ?>
            
            <p><input name="Submit" class="button-primary" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" /></p>
            </form>
        <?php
    }
    
    function settings_init() {
        add_settings_section( 'openstates', '',
            array(&$this, 'settings_section'), 'openstates');
        
        add_settings_field('sunlight_apikey', 'Sunlight API Key',
            array(&$this, 'apikey_field'), 'openstates', 'openstates');
        register_setting('openstates', 'sunlight_apikey');
    }
    
    function settings_section() {}
    
    function apikey_field() {
        $option = get_option('sunlight_apikey', ''); ?>
        <input type="text" value="<?php echo esc_attr($option); ?>" name="sunlight_apikey">
        <?php
    }
    
}

new Navis_OpenStates;

?>