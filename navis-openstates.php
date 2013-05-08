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
    
        // shortcode to render legislator
        add_shortcode('legislator', array(&$this, 'legislator_shortcode'));
        
        // shortcode to render bill
        add_shortcode('bill', array(&$this, 'bill_shortcode'));
        
        // buttons
        add_action('init', array(&$this, 'register_tinymce_filters'));
        
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
        
        // ajax
        add_action('wp_ajax_get_legislator_form', array(&$this, 'get_legislator_form'));
        add_action('wp_ajax_get_bill_form', array(&$this, 'get_bill_form'));
        
        // include the api key
        add_action('admin_head', array(&$this, 'insert_api_key'));
        
    }
    
    function insert_api_key() { ?>
        <script>
        window.sunlight_apikey = "<?php echo get_option('sunlight_apikey'); ?>";
        </script><?php
    }
    
    function register_tinymce_filters() {
        add_filter('mce_external_plugins', 
            array(&$this, 'add_tinymce_plugins')
        );
        add_filter('mce_buttons', 
            array(&$this, 'register_buttons')
        );
    }
    
    function add_tinymce_plugins($plugin_array) {
        $plugin_array['openstates_legislators'] = plugins_url(
            'js/tinymce/legislators-tinymce.js', __FILE__);
        $plugin_array['openstates_bills'] = plugins_url(
            'js/tinymce/bills-tinymce.js', __FILE__);
        return $plugin_array;
    }
    
    function register_buttons($buttons) {
        array_push($buttons, '|', "openstates_legislators", "openstates_bills");
        return $buttons;
    }
    
    function get_bill_form() { ?>
        <div id="bill-search" class="openstates">
            <div class="form">
                <form class="bill-search">
                    <p>
                        <select name="state" class="state">
                            <option value="">State</option>
                            <option value="AL">Alabama</option>
                            <option value="AK">Alaska</option>
                            <option value="AZ">Arizona</option>
                            <option value="AR">Arkansas</option>
                            <option value="CA">California</option>
                            <option value="CO">Colorado</option>
                            <option value="CT">Connecticut</option>
                            <option value="DE">Delaware</option>
                            <option value="DC">District of Columbia</option>
                            <option value="FL">Florida</option>
                            <option value="GA">Georgia</option>
                            <option value="HI">Hawaii</option>
                            <option value="ID">Idaho</option>
                            <option value="IL">Illinois</option>
                            <option value="IN">Indiana</option>
                            <option value="IA">Iowa</option>
                            <option value="KS">Kansas</option>
                            <option value="KY">Kentucky</option>
                            <option value="LA">Louisiana</option>
                            <option value="ME">Maine</option>
                            <option value="MD">Maryland</option>
                            <option value="MA">Massachusetts</option>
                            <option value="MI">Michigan</option>
                            <option value="MN">Minnesota</option>
                            <option value="MS">Mississippi</option>
                            <option value="MO">Missouri</option>
                            <option value="MT">Montana</option>
                            <option value="NE">Nebraska</option>
                            <option value="NV">Nevada</option>
                            <option value="NH">New Hampshire</option>
                            <option value="NJ">New Jersey</option>
                            <option value="NM">New Mexico</option>
                            <option value="NY">New York</option>
                            <option value="NC">North Carolina</option>
                            <option value="ND">North Dakota</option>
                            <option value="OH">Ohio</option>
                            <option value="OK">Oklahoma</option>
                            <option value="OR">Oregon</option>
                            <option value="PA">Pennsylvania</option>
                            <option value="RI">Rhode Island</option>
                            <option value="SC">South Carolina</option>
                            <option value="SD">South Dakota</option>
                            <option value="TN">Tennessee</option>
                            <option value="TX">Texas</option>
                            <option value="UT">Utah</option>
                            <option value="VT">Vermont</option>
                            <option value="VA">Virginia</option>
                            <option value="WA">Washington</option>
                            <option value="WV">West Virginia</option>
                            <option value="WI">Wisconsin</option>
                            <option value="WY">Wyoming</option>
                        </select>
                        
                        <label for="q">Text search</label>
                        <input type="text" class="q" name="q">
                        <input type="button" class="button search" value="Search">
                    </p>
                </form>
            </div>
            <div class="results"></div>
        </div>
        <?php
        die();
    }
    
    function get_legislator_form() { ?>
        <div id="legislator-search" class="openstates">
            <div class="form">
                <form class="legislator-search">
                    <p>
                        <label for="state">State</label>
                        <select name="state" class="state">
                            <option value=""> --- </option>
                            <option value="AL">Alabama</option>
                            <option value="AK">Alaska</option>
                            <option value="AZ">Arizona</option>
                            <option value="AR">Arkansas</option>
                            <option value="CA">California</option>
                            <option value="CO">Colorado</option>
                            <option value="CT">Connecticut</option>
                            <option value="DE">Delaware</option>
                            <option value="DC">District of Columbia</option>
                            <option value="FL">Florida</option>
                            <option value="GA">Georgia</option>
                            <option value="HI">Hawaii</option>
                            <option value="ID">Idaho</option>
                            <option value="IL">Illinois</option>
                            <option value="IN">Indiana</option>
                            <option value="IA">Iowa</option>
                            <option value="KS">Kansas</option>
                            <option value="KY">Kentucky</option>
                            <option value="LA">Louisiana</option>
                            <option value="ME">Maine</option>
                            <option value="MD">Maryland</option>
                            <option value="MA">Massachusetts</option>
                            <option value="MI">Michigan</option>
                            <option value="MN">Minnesota</option>
                            <option value="MS">Mississippi</option>
                            <option value="MO">Missouri</option>
                            <option value="MT">Montana</option>
                            <option value="NE">Nebraska</option>
                            <option value="NV">Nevada</option>
                            <option value="NH">New Hampshire</option>
                            <option value="NJ">New Jersey</option>
                            <option value="NM">New Mexico</option>
                            <option value="NY">New York</option>
                            <option value="NC">North Carolina</option>
                            <option value="ND">North Dakota</option>
                            <option value="OH">Ohio</option>
                            <option value="OK">Oklahoma</option>
                            <option value="OR">Oregon</option>
                            <option value="PA">Pennsylvania</option>
                            <option value="RI">Rhode Island</option>
                            <option value="SC">South Carolina</option>
                            <option value="SD">South Dakota</option>
                            <option value="TN">Tennessee</option>
                            <option value="TX">Texas</option>
                            <option value="UT">Utah</option>
                            <option value="VT">Vermont</option>
                            <option value="VA">Virginia</option>
                            <option value="WA">Washington</option>
                            <option value="WV">West Virginia</option>
                            <option value="WI">Wisconsin</option>
                            <option value="WY">Wyoming</option>
                        </select>
                    </p>
                    <p>
                        <label>First Name</label>
                        <input type="text" class="first_name" name="first_name">
                        <br>
                        <label>Last Name</label>
                        <input type="text" class="last_name" name="last_name">
                    </p>
                    <p>
                        <label for="chamber">Chamber</label>
                        <select name="chamber" class="chamber">
                            <option value="">---</option>
                            <option value="upper">Upper</option>
                            <option value="lower">Lower</option>
                        </select>
                    </p>
                    <p>
                        <label for="district">District</label>
                        <input type="text" class="district" name="district">
                    </p>
                    <p>
                        <label for="party">Party</label>
                        <input type="text" class="party" name="party">
                    </p>
                    <p><input type="button" class="search button" value="Search"></p>
                </form>
            </div>
            <div class="results"></div>
        </div>
        <?php
        die();
    }
        
    function fetch($url) {
        $response = wp_remote_retrieve_body( wp_remote_get($url) );
        if (empty($response)) {
            return false;
        }
        return json_decode($response, 'true');
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
    
    function get_latest_action($bill) {
        if ($bill['actions']) {
            $latest = array_pop($bill['actions']);
            $action = array(
                'action' => $latest['action'],
                'date' => date('M j, Y', strtotime($latest['date']))
            );
            return $action;            
        }
    }
    
    function bill_shortcode($atts) {
        extract(shortcode_atts(array(
            'state' => null,
            'session' => null,
            'id' => null,
            'align' => 'left'
        ), $atts));
        $apikey = get_option('sunlight_apikey');
        
        if (!($state && $session && $id && $apikey)) return;
        
        $qs = http_build_query(array( 'apikey' => $apikey ));
        $url = "http://openstates.org/api/v1/bills/$state/$session/$id/?" . $qs;
        $bill = $this->fetch($url);
        if (!$bill) {
            error_log('No data returned from '.$url);
            return;
        }
        
        $align = $this->get_alignment($align);
        $morelink = $bill['versions'][0]['url'];
        $last_action = $this->get_latest_action($bill);
        
        $html  = "<div class=\"openstates-module bill-tracker $align\">";
        $html .=    "<h2 class=\"module-title\">Bill Tracker</h2>";
        $html .=    "<div class=\"box-wrapper\">";

        $html .=        "<h5 class=\"info-hed\"><a target=\"_blank\" href=\"$morelink\">{$bill['bill_id']}</a></h5>";
        $html .=        "<p>{$bill['title']}</p>";
        $html .=        "<h5 class=\"info-hed\">Latest Action</h5>";
        $html .=        "<p>{$last_action['date']}: {$last_action['action']}</p>";
        $html .=        "<p><a target=\"_blank\" class=\"jump-link\" href=\"$morelink\">Read the bill &raquo;</a></p>";

        $html .=        "<p class=\"source\">Source: <a href=\"http://openstates.org/\">Open States</a></p>";
        $html .=    "</div>";
        $html .= "</div>";
        
        return $html;
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
        $member = $this->fetch($url);
        
        $classname = $this->get_alignment($align);
        $title = $member['chamber'] == 'upper' ? 'Sen. ' : 'Rep. ';
        $displayname = $title . $member['full_name'] . " ({$member['party'][0]})";
        $committees = $this->get_committees($member);
        
        $html  = "<div class=\"openstates-module legislator $classname\">";
        $html .=    "<h2 class=\"module-title\">Legislator Info</h2>";
        $html .=    "<div class=\"box-wrapper\">";
        $html .=        "<h3 class=\"name\"><a target=\"_blank\" href=\"{$member['url']}\">$displayname</a></h3>";
        $html .=        "<h4 class=\"district\">District {$member['district']}</h4>";
        if ($committees) {
            $html .=        "<h5 class=\"info-hed\">Committees</h5>";
            $html .=        "<p>". implode(', ', $committees) . "</p>";
        }
        if ($member['+address'] || $member['+phone_number']) {
            $html .=        "<h5 class=\"info-hed\">Contact</h5>";
            $html .=        "<p>{$member['+address']}<br>";
            $html .=        "{$member['+phone_number']}</p>";
        }
        
        $html .=        "<p><a target=\"_blank\" class=\"jump-link\" href=\"{$member['url']}\">More &raquo;</a></p>";
        $html .=        "<p class=\"source\">Source: <a href=\"http://openstates.org/\">Open States</a></p>";
        $html .=    "</div>";
        $html .= "</div>";
        
        return $html;
    }
    
    function get_committees($leg_data) {
        $committees = array();
        foreach((array)$leg_data['roles'] as $role) {
            if ($role['type'] == 'committee member') {
                $committees[] = $role['committee'];
            }
        }
        return $committees;
    }
    
    function add_admin_stylesheet() {
        $css = plugins_url('css/openstates.css', __FILE__);
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_style(
            'openstates', $css, array(), '0.1'
        );
    }
    
    function register_admin_scripts($atts) {
        $js = array(
            'underscore-old' => plugins_url('js/underscore-min.js', __FILE__),
            'backbone-old' => plugins_url('js/backbone-min.js', __FILE__),
            'backbone-modelbinding' => plugins_url('js/backbone.modelbinding.min.js', __FILE__),
            'openstates-legislators' => plugins_url('js/openstates/legislators.js', __FILE__),
            'openstates-bills' => plugins_url('js/openstates/bills.js', __FILE__)
        );

        wp_enqueue_script( 'underscore-old', $js['underscore-old']);
        wp_enqueue_script( 'backbone-old', $js['backbone-old'],
            array('underscore', 'jquery'));
        wp_enqueue_script( 'backbone-modelbinding', $js['backbone-modelbinding'],
            array('underscore-old', 'backbone-old', 'jquery'));
        wp_enqueue_script( 'openstates-legislators', $js['openstates-legislators'], 
            array('underscore-old', 'backbone-old', 'jquery', 'backbone-modelbinding'), '0.1');
        wp_enqueue_script( 'openstates-bills', $js['openstates-bills'], 
            array('underscore-old', 'backbone-old', 'jquery', 'backbone-modelbinding'), '0.1');        
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