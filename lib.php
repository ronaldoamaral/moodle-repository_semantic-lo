<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This plugin is used to access Semantic LO
 *
 * @since 0.1
 * @package    repository_semantic_lo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once($CFG->dirroot . '/repository/lib.php');

/**
 * repository_semantic_lo class
 *
 * @since 0.1
 * @package    repository_semantic_lo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class repository_semantic_lo extends repository {
    /**
     * Semantic LO plugin constructor
     * @param int $repositoryid
     * @param object $context
     * @param array $options
     */
    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
        parent::__construct($repositoryid, $context, $options);
        $this->semantic_lo_service = $this->get_option('semantic_lo_service');
        $this->semantic_lo_repository = $this->get_option('semantic_lo_repository');
    }
     /**
     * Return names of the options to display in the repository instance form
     *
     * @return array of option names
     */
    public static function get_instance_option_names() {
        return array('semantic_lo_service', 'semantic_lo_repository');
    }
    public static function instance_config_form($mform) {
        $strrequired = get_string('required');
        $mform->addElement('text', 'semantic_lo_service', get_string('semantic_lo_service', 'repository_semantic_lo'));
        $mform->addRule('semantic_lo_service', $strrequired, 'required', null, 'client');
        $mform->setType('semantic_lo_service', PARAM_URL);
        $mform->setDefault('semantic_lo_service', 'http://localhost:5000/');
        $mform->addElement('text', 'semantic_lo_repository', get_string('semantic_lo_repository', 'repository_semantic_lo'));
        $mform->addRule('semantic_lo_repository', $strrequired, 'required', null, 'client');
        $mform->setType('semantic_lo_repository', PARAM_URL);
        $mform->setDefault('semantic_lo_repository', 'http://localhost:8080/semanticlo/resource/');
    }

    
    public function check_login() {
        return !empty($this->keyword);
    }
    
    /**
    public function print_search() {
 
    // label search name
    $param = array('for' => 'label_search_name');
    $title = get_string('search_name', 'myrepo_search_name');
    $html .= html_writer::tag('label', $title, $param);
    $html .= html_writer::empty_tag('br');
 
    // text field search name
    $attributes['type'] = 'text';
    $attributes['name'] = 's';
    $attributes['value'] = '';
    $attributes['title'] = $title;
    $html .= html_writer::empty_tag('input', $attributes);
    $html .= html_writer::empty_tag('br');
 
    return $html;
    }
    */

    /**
     * Return search results
     * @param string $search_text
     * @return array
     */
    public function search($search_text, $page = 0) {       
        $ret  = array();
        //$this->keyword = $search_text;
        //$uri = optional_param('semanticlo_search_uri', '', PARAM_TEXT);        
        $ret['nologin'] = true;
        $ret['page'] = (int)$page;
        $ret['list'] = $this->_get_collection($search_text);
        $ret['norefresh'] = true;
        $ret['nosearch'] = true;
        return $ret;
    }

    /**
     * Private method to get Semantic LO search results
     * @param string $keyword
     * @return array
     */
    private function _get_collection($keyword) {
        $list = array();       
        
        $this->query_string = $this->semantic_lo_service . 'search?title=' . urlencode($keyword);
        
        $c = new curl();
        $resp = $c->get($this->query_string);
        
        $results = json_decode($resp, true);
        $results_objects_head =  $results['head']['vars'];
        $results_objects_list = $results['results']['bindings'];

        foreach ($results_objects_list as $entry) {
            $source = $entry['identifier']['value']; 
            $title = $entry['title']['value'];
            $description = '';
            if (empty($description)) {
                $description = $title;
            }

            $list[] = array(
                'shorttitle'=>$title,
                'thumbnail_title'=>$description,
                'title'=>$title,
                'thumbnail'=>'',
                'thumbnail_width'=>0,
                'thumbnail_height'=>0,
                'size'=>'',
                'date'=>'',
                'source'=>$source,
            );
        }
        return $list;
    }

    /**
     * Semantic LO plugin doesn't support global search
     */
    public function global_search() {
        return false;
    }

    public function get_listing($path='', $page = '') {
        return array();
    }

    /**
     * Generate search form
    */
    public function print_login($ajax = true) {
        $ret = array();
        $search = new stdClass();
        $search->type = 'text';
        $search->id   = 'semanticlo_search_title';
        $search->name = 's';
        $search->label = get_string('title', 'repository_semantic_lo').': ';
        
        $uri->type = 'text';
        $uri->id   = 'semanticlo_search_uri';
        $uri->name = 'semanticlo_search_uri';
        $uri->label = get_string('uri', 'repository_semantic_lo').': ';
        
        $ret['login'] = array($search, $uri);
        $ret['login_btn_label'] = get_string('search');
        $ret['login_btn_action'] = 'search';
        $ret['allowcaching'] = false; // indicates that login form can be cached in filepicker.js
        return $ret;
    }
    
    /**
     * file types supported by Semantic LO plugin
     * @return array
     */
    public function supported_filetypes() {
        return array('*');
    }

    /**
     * Semantic LO plugin only return external links
     * @return int
     */
    public function supported_returntypes() {
        return (FILE_INTERNAL | FILE_EXTERNAL);
    }

    /**
     * Is this repository accessing private data?
     *
     * @return bool
     */
    public function contains_private_data() {
        return false;
    }
}
