<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// define the old-style EE object
if (!function_exists('ee')) {
  function ee() {
    static $EE;
    if (! $EE) {
      $EE = get_instance();
    }
    return $EE;
  }
}

/**
 * BugHerd Sidebar installation
 *
 * @package     Bugherd
 * @version     1.0
 * @author      Tommy-Carlos Williams <http://bugherd.com> - Senior Software Engineer - BugHerd
 * @copyright     Copyright (c) 2012 BugHerd <http://bugherd.com>
 * @license     MIT
 * @link      http://bugherd.com
 */
class Bugherd_ext {

  var $name         = 'BugHerd';
    var $version        = '0.1';
    var $description    = 'Installs BugHerd Sidebar';
    var $settings_exist = 'y';
    var $docs_url       = 'http://docs.bugherd.com/';

    var $settings = array();

    /**
     * Constructor
     *
     * @param   mixed   Settings array or empty string if none exist.
     */
    function __construct($settings='') {
      ee()->lang->loadfile('bugherd');

      $this->name = lang('name');
      $this->description = lang('description');

      $this->settings = $settings;
    }
    

    /**
   * Activate Extension
   *
   * This function enters the extension into the exp_extensions table
   *
   * @see http://codeigniter.com/user_guide/database/index.html for
   * more information on the db class.
   *
   * @return void
   */
  function activate_extension() {
    $this->settings = array(
        'api_key'   => ''
    );

    // front end
    $data = array(
      'class'   => __CLASS__,
      'method'  => 'add_bugherd_frontend',
      'hook'    => 'template_post_parse',
      'settings' => serialize($this->settings),
      'version' => $this->version,
      'enabled' => 'y'
    );
    ee()->db->insert('extensions', $data);

    // control panel
    $data['method'] = 'add_bugherd_cp';
    $data['hook'] = 'cp_js_end';
    ee()->db->insert('extensions', $data);
  }

  /**
   * Update Extension
   *
   * This function performs any necessary db updates when the extension
   * page is visited
   *
   * @return  mixed   void on update / false if none
   */
  function update_extension($current = '') {
    if ($current == '' OR $current == $this->version) {
        return FALSE;
    }

    if ($current < '1.0') {
        // Update to version 1.0
    }

    ee()->db->where('class', __CLASS__);
    ee()->db->update(
                'extensions',
                array('version' => $this->version)
              );
  }

  /**
   * Disable Extension
   *
   * This method removes information from the exp_extensions table
   *
   * @return void
   */
  function disable_extension() {
    ee()->db->where('class', __CLASS__);
    ee()->db->delete('extensions');
  }

  // --------------------------------
  //  Settings
  // --------------------------------

  function settings() {
    $settings = array();

    // Creates a text input with a default value
    $settings['api_key'] = array('i', '', 'your api key here');

    return $settings;
  }

  /**
   * Settings Form
   *
   * @param   Array   Settings
   * @return  void
   */
  function settings_form($current) {
    ee()->load->helper('form');
    ee()->load->library('table');

    $vars = array();

    $api_key = isset($current['api_key']) ? $current['api_key'] : "";

    $vars['settings'] = array(
        'api_key'   => form_input('api_key', $api_key)
    );

    return ee()->load->view('index', $vars, TRUE);
  }

  /**
   * Save Settings
   *
   * This function provides a little extra processing and validation
   * than the generic settings form.
   *
   * @return void
   */
  function save_settings() {
    if (empty($_POST)) {
        show_error(ee()->lang->line('unauthorized_access'));
    }

    unset($_POST['submit']);

    ee()->lang->loadfile('bugherd');

    $len = ee()->input->post('api_key');

    ee()->db->where('class', __CLASS__);
    ee()->db->update('extensions', array('settings' => serialize($_POST)));

    ee()->session->set_flashdata(
      'message_success',
      ee()->lang->line('preferences_updated')
    );
  }

  private function get_snippet() {
    $api_key = $this->get_key();
    if (empty($api_key)) {
      return null;
    }
    ob_start();
    include('bugherd-snippet.php');
    $javascript = ob_get_clean();
    
    return $javascript;
  }

  private function get_key() {
    //grab the cached settings if they're not already there for some reason
    if (empty($this->settings)) {
      $this->settings = ee()->extensions->s_cache[__CLASS__];
    }
    return $this->settings['api_key'];
  }

  /**
   * clean_template
   *
   * @param 
   * @return 
   */
  public function add_bugherd_frontend($template, $sub,  $site_id) {
    // //only run on the final template
    if ($sub) {
      return $template;
    }

    if (ee()->extensions->last_call !== FALSE) {
      $template = ee()->extensions->last_call;
    }
    
    $template = ee()->TMPL->parse_globals($template);

    $javascript = $this->get_snippet();
    if (!is_null($javascript)) {
      $template = str_ireplace("</body>", $javascript, $template);
    }

    return $template;
  }

  public function add_bugherd_cp() {
    if (ee()->extensions->last_call !== FALSE) {
      $output = ee()->extensions->last_call;
    }

    $javascript = $this->get_snippet();
    if (!is_null($javascript)) {
      $output .= $javascript;
    }

    return $output;
  }
}
// END CLASS
/* End of file ext.bugherd.php */
/* Location: ./system/expressionengine/third_party/bugherd/ext.bugherd.php */